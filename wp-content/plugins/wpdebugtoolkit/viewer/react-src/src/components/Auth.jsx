import React, { useState, useEffect } from "react";
import { Eye, EyeOff, Shield, Lock, Loader2 } from "lucide-react";
import Logo from "../assets/logo.png";
import { authenticate } from "../services/api";

const Auth = ({ onAuthenticated }) => {
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // Add keyboard event handler for Escape key
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === "Escape" && !isLoading) {
        // Clear form and errors when Escape is pressed
        setPassword("");
        setError(null);
      }
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [isLoading]);

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!password) {
      setError("Please enter a password");
      return;
    }

    setIsLoading(true);
    setError(null);

    try {
      const success = await authenticate(password);

      if (success) {
        // Authentication successful
        setIsLoading(false);
        onAuthenticated();
      } else {
        // Authentication failed
        setError("Invalid password");
        setIsLoading(false);
      }
    } catch (error) {
      setError("Failed to authenticate. Please try again.");
      setIsLoading(false);
    }
  };

  return (
    <div
      className={`fixed inset-0 flex items-center justify-center bg-gray-900 p-4 transition-opacity`}
    >
      <div
        className={`w-full max-w-md overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-800`}
      >
        <div className="p-6 sm:p-8">
          <div className="mb-8 flex flex-col items-center">
            <img src={Logo} alt="Debug Toolkit Logo" className="mb-4 size-16" />
            <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
              WP Debug Toolkit
            </h1>
            <p className="mt-2 text-center text-gray-500 dark:text-gray-400">
              Password protected viewer
            </p>
          </div>

          <div className="mb-6 flex items-start rounded-lg bg-blue-50 p-4 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
            <Shield className="mr-3 mt-0.5 size-5 shrink-0" />
            <p className="text-sm">
              This debug log viewer is password protected to keep your sensitive
              information secure.
            </p>
          </div>

          {error && (
            <div className="mb-6 rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-900/20 dark:text-red-300">
              <p className="text-sm">{error}</p>
            </div>
          )}

          <form onSubmit={handleSubmit}>
            <div className="mb-4">
              <label
                htmlFor="password"
                className="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
              >
                Password
              </label>
              <div className="relative">
                <input
                  type={showPassword ? "text" : "password"}
                  id="password"
                  className="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-pink-500 focus:ring-2 focus:ring-pink-500 dark:border-gray-700 dark:bg-gray-700 dark:text-white"
                  placeholder="Enter your password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  disabled={isLoading}
                  /* eslint-disable-next-line jsx-a11y/no-autofocus */
                  autoFocus
                  // Add keyDown handler for Enter key - redundant as form already handles submission on Enter
                  onKeyDown={(e) => {
                    if (e.key === "Enter" && !isLoading) {
                      e.preventDefault();
                      handleSubmit(e);
                    }
                  }}
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  disabled={isLoading}
                >
                  {showPassword ? (
                    <EyeOff className="size-5" />
                  ) : (
                    <Eye className="size-5" />
                  )}
                </button>
              </div>
            </div>

            <button
              type="submit"
              className="flex w-full items-center justify-center rounded-md bg-pink-600 px-4 py-2 font-medium text-white transition-colors hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
              disabled={isLoading}
            >
              {isLoading ? (
                <>
                  <Loader2 className="mr-2 size-5 animate-spin" />
                  Authenticating...
                </>
              ) : (
                <>
                  <Lock className="mr-2 size-5" />
                  Log In
                </>
              )}
            </button>
          </form>
        </div>

        <div className="bg-gray-50 px-6 py-4 text-center text-xs text-gray-500 dark:bg-gray-900 dark:text-gray-400">
          WP Debug Toolkit • Password Protected Mode
        </div>
      </div>
    </div>
  );
};

export default Auth;
