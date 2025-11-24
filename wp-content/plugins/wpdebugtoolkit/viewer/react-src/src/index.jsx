import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";
import "@/styles/globals.css";
import { ToastProvider } from "./components/ui/toast";

// Set dark mode as default
document.documentElement.classList.add("dark");

const container = document.getElementById("root");
const root = createRoot(container);

root.render(
  <React.StrictMode>
    <ToastProvider>
      <App />
    </ToastProvider>
  </React.StrictMode>,
);
