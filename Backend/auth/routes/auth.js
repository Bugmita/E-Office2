import express from "express";
import bcrypt from "bcrypt";
import jwt from "jsonwebtoken";
import pool from "../db.js";
import nodemailer from "nodemailer";
import dotenv from "dotenv";
import validator from "validator";


dotenv.config();

const router = express.Router();

// send Mail for verification

const transporter = nodemailer.createTransport(
  {
    service: "Gmail",
    auth:{
      user: process.env.USER_EMAIL,
      pass: process.env.USER_PASS
    }
  }
);

async function sendEmail(to, subject, html){
  console.log(to);
  await transporter.sendMail({
    from: process.env.USER_EMAIL,
    to, subject, html
  });
}

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

    if (!validator.isEmail(email)) {
      return res.status(400).json({ message: "Invalid email format" });
    }

    if(!email.endsWith("nits.ac.in")){
      return res.status(400).json({message: "Email must end with nits.ac.in"});
    }

    const hashedPassword = await bcrypt.hash(password, 10);

    const [data] = await pool.query(
      "INSERT INTO users (name, email, role, password, is_Verified) VALUES (?, ?, ?, ?, 0)",
      [name, email, role, hashedPassword]
    );

    const userId = data.insertId;

    const token = jwt.sign({id: userId}, process.env.JWT_SECRET, {expiresIn : "1h"});
    const verifyLink = `http://localhost:5000/auth/verify/${token}`;

    await sendEmail(
      email,
      "Verify your account",
      `<p>Hello ${name}, </p>
      <p> Click here to verify your account: </p>
      <a href="${verifyLink}">${verifyLink}</a>`
    );

    res.status(201).json({ message: "Signup successful. Please verify you email" });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// email Verification

router.get("/verify/:token", async(req, res) =>{
  try{
    const {token} = req.params;
    const decoded = jwt.verify(token, process.env.JWT_SECRET);

    await pool.query("UPDATE users SET is_verified = 1 WHERE id = ?", [decoded.id]);

    res.redirect("/final.html");
  }catch(err){
    res.send("Email verification failed");
  }
})

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
