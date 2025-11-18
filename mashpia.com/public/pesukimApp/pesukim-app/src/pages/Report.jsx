import React, { useState } from "react";
import "./Report.css";
import logo from "../assets/images/tzivos-hashem-logo.png";

function setCookie(name, value, days) {
  const d = new Date();
  d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
  const expires = `expires=${d.toUTCString()}`;
  document.cookie = `${name}=${encodeURIComponent(value)}; ${expires}; path=/`;
}

export default function Report() {
  const [loading, setLoading] = useState(false);
  const [err, setErr] = useState("");
  const [debugText, setDebugText] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    e.stopPropagation(); // belt & suspenders to stop default submit
    setErr("");
    setDebugText("");

    if (loading) return;

    const f = e.currentTarget;
    const username = f.username.value.trim();
    const password = f.password.value;
    const remember = f.remember.checked;

    if (!username || !password) {
      setErr("Please enter a username and password.");
      return;
    }

    setLoading(true);
    try {
      const body = new URLSearchParams({ username, password }).toString();

      const res = await fetch("/mobile/reg/ajax/auth.php", {
        method: "POST",
        // Match jQuery defaults closely:
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "text/plain, */*;q=0.1",
        },
        body,
        credentials: "same-origin",
        redirect: "follow",
      });

      const text = (await res.text() || "").trim();
      setDebugText(`server said: "${text}"`);

      // Success condition: any non-"0" string
      if (text !== "0" && text !== "") {
        // Store the user token
        setCookie("admin", text, remember ? 365 : 1);
        // Go to parent detail page
        window.location.href = "/mobile/reg/parent_detail.html";
        return;
      }

      setErr("Incorrect username or password. Please try again.");
    } catch (ex) {
      setErr("Login failed. Please try again in a moment.");
      setDebugText(String(ex));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="report-wrap">
      <div className="card card-navy report-card">
      <h1 className="title-text">REPORT YOUR ACCOMPLISHMENTS</h1>
      <p className="body-text text-center">Family Account Login</p>

      <img src={logo} alt="Tzivos Hashem logo" className="report-logo" />

      <form className="report-form" onSubmit={handleSubmit}>
        <div className="input-wrap">
          <input id="username" name="username" type="text" placeholder="USERNAME" autoComplete="username" />
        </div>

        <div className="input-wrap">
          <input id="password" name="password" type="password" placeholder="PASSWORD" autoComplete="current-password" />
        </div>

        <div className="remember-row">
          <label className="remember">
            <input type="checkbox" id="remember" name="remember" />
            <span className="checkbox-skin" aria-hidden="true"></span>
            <span className="remember-label">Remember me</span>
          </label>
        </div>

        {err && <div className="report-error" role="alert">{err}</div>}

        <button type="submit" className="btn tertiary m-auto" disabled={loading}>
          {loading ? "LOGGING IN…" : "LOGIN"}
        </button>

        <a className="btn muted m-auto small" href="/mobile/reg/forgot.html">
          FORGOT USER/PASSWORD
        </a>
      </form>
    </div>
    </div>
  );
}
