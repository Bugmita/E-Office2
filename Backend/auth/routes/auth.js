import express, { response } from "express";
import bcrypt from "bcrypt";
import jwt from "jsonwebtoken";
import pool from "../db.js";

const router = express.Router();

// signup
router.post("/signup", async (req, res) => {
  const { name, email, role, password, confirmPassword } = req.body;

  if(password !== confirmPassword){
    return res.status(400).json({message: "Passwords do not match"});
  }

  try {
    const [rows] = await pool.query("SELECT * FROM users WHERE email = ?",
      [email]
    );

    if(rows.length>0){
      return res.status(400).json({message: "User already exists"});
    }
    

    const hashedPassword = await bcrypt.hash(password, 10);

    await pool.query(
      "INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)",
      [name, email, role, hashedPassword]
    );

    res.status(201).json({ message: "User registered" });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// login
router.post("/login", async (req, res) => {
  const { email, password } = req.body;

  try {
    const [rows] = await pool.query("SELECT * FROM users WHERE email = ?", [
      email,
    ]);

    if (rows.length === 0)
      return res.status(400).json({ message: "User not found" });

    const user = rows[0];

    const isMatch = await bcrypt.compare(password, user.password);

    if (!isMatch)
      return res.status(400).json({ message: "Invalid credentials" });

    const token = jwt.sign(
      { id: user.id, email: user.email },
      process.env.JWT_SECRET,
      { expiresIn: "1h" }
    );

    res
      .cookie("token", token, { httpOnly: true })
      .json({ message: "Login successful" });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

export default router;
