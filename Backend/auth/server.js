import express from "express";
import cookieParser from "cookie-parser";
import authRoutes from "./routes/auth.js";
import path from "path";
import { fileURLToPath } from "url";

const app = express();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

app.use(express.static(path.join(__dirname, "../../FrontEnd/Auth")));

app.use(express.json());
app.use(cookieParser());

app.use("/auth", authRoutes);

const PORT = 5000;
app.listen(PORT, () =>
  console.log(`Server running on http://localhost:${PORT}`)
);
