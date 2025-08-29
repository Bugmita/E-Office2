import express from "express";
import cookieParser from "cookie-parser";
import authRoutes from "./routes/auth.js";

const app = express();

app.use(express.json());
app.use(cookieParser());

app.use("/auth", authRoutes);

const PORT = 5000;
app.listen(PORT, () =>
  console.log(`Server running on http://localhost:${PORT}`)
);
