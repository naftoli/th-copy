import React, { useEffect, useRef, useState } from "react";
import "./Join.css";

import confettiImg from "../assets/images/confetti.png";
import calendarIcon from "../assets/images/calendar-3d.svg";


const initialForm = {
  firstName: "",
  lastName: "",
  dob: "",
  parentEmail: "",
  parentPhone: "",
  referral: "",
};

// Field component moved outside to prevent recreation on each render
const Field = ({ id, label, icon, value, onChange, error, ...rest }) => (
  <div className={`join-field ${icon ? 'has-icon' : ''}`}>
    <input
      id={id}
      name={id}
      value={value}
      onChange={onChange}
      {...rest}
    />
    {icon && <img src={icon} alt="" className="field-icon" />}
    {error && <em className="err">{error}</em>}
  </div>
);

const isValidDate = (date) => {
  return !isNaN(new Date(date).getTime());
};

const Join = () => {
  const [step, setStep] = useState("pledge");
  const [pledged, setPledged] = useState(false);
  const [form, setForm] = useState(initialForm);
  const [errors, setErrors] = useState({});
  const modalRef = useRef(null);
  const [submitting, setSubmitting] = useState(false);


  // basic validators (you can swap with your own later)
  const validate = () => {
    const e = {};
    if (!form.firstName.trim()) e.firstName = "Required";
    if (!form.lastName.trim()) e.lastName = "Required";
    // check if the dob is a valid date
    if (!isValidDate(form.dob)) e.dob = "Invalid date";
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(form.parentEmail))
      e.parentEmail = "Enter a valid email";
    if (!/^[0-9\-+\s()]{7,}$/.test(form.parentPhone))
      e.parentPhone = "Enter a valid phone";
    return e;
    // referral is optional
  };

  const onSubmit = async (e) => {
    e.preventDefault();
  
    const eMap = validate();
    setErrors(eMap);
    if (Object.keys(eMap).length) return;
  
    setSubmitting(true);
    try {
      const res = await fetch('/api/pesukim/join-submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
  
      // Try to parse JSON either way
      const data = await res.json().catch(() => ({}));
  
      if (!res.ok || data?.ok === false) {
        // If backend returns field errors, show them inline
        const fieldErrs = data?.error?.fields || {};
        if (Object.keys(fieldErrs).length) {
          setErrors(fieldErrs);
          return;
        }
        // Fallback message
        alert(data?.error?.message || 'Submission failed. Please try again.');
        return;
      }
  
      // Success!
      setStep('done');
    } catch (err) {
      console.error(err);
      alert('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  // Trap focus in modal when open
  useEffect(() => {
    if (step !== "done") return;
    const prev = document.activeElement;
    modalRef.current?.focus();
    const handleKey = (ev) => {
      if (ev.key === "Escape") setStep("form");
    };
    document.addEventListener("keydown", handleKey);
    return () => {
      document.removeEventListener("keydown", handleKey);
      prev?.focus?.();
    };
  }, [step]);

  const resetPage = () => {
    setStep("pledge");
    setPledged(false);
    setForm(initialForm);
    setErrors({});
  };

  return (
    <div className="join-wrap">
      {/* STEP 1: PLEDGE */}
      {step === "pledge" && (
        <div className="join-card">
          <h1 className="title-text">JOIN</h1>
          <div className="pledge-row">
            <label className="pledge-check">
              <input
                type="checkbox"
                checked={pledged}
                onChange={(e) => setPledged(e.target.checked)}
              />
              <span className="box" aria-hidden="true" />
              <span className="body-text">
                I would like to join the movement of hundreds of thousands of
                Jewish children around the world who are learning and saying the
                12 Pessukim everywhere they go and teaching them to their
                friends.
              </span>
            </label>
          </div>

          {pledged && (
            <div className="join-actions">
              <button
              className="btn primary small"
              onClick={() => setStep("form")}
              >
                continue
              </button>
            </div>
          )}
        </div>
      )}

      {/* STEP 2: FORM */}
      {["form", "done"].includes(step) && (
        <div className="join-card form">
          <div className="go-back">
            <button
              onClick={resetPage}
            >
              X
            </button>
          </div>
          <h1 className="title-text">SIGN UP FORM</h1>

          <p className="body-text">
            Please create an account for me to join the Tzivos Hashem 12
            Pessukim campaign, in which I can log my accomplishments and win
            incredible prizes:
          </p>

          <form onSubmit={onSubmit} className="join-form" noValidate>
            <h2 className="body-text">Child's Information</h2>
            <div className="grid2">
              <Field 
                id="firstName" 
                placeholder="First name"
                value={form.firstName}
                onChange={(e) => setForm({ ...form, firstName: e.target.value })}
                error={errors.firstName}
              />
              <Field 
                id="lastName" 
                placeholder="Last name"
                value={form.lastName}
                onChange={(e) => setForm({ ...form, lastName: e.target.value })}
                error={errors.lastName}
              />
            </div>

            <div className="grid1">
              <Field
                id="dob"
                placeholder="DOB MM/DD/YYYY"
                type="date"
                icon={calendarIcon}
                value={form.dob}
                onChange={(e) => setForm({ ...form, dob: e.target.value })}
                error={errors.dob}
              />
            </div>

            <h2 className="body-text">Parent's Information</h2>
            <div className="grid2">
              <Field
                id="parentEmail"
                placeholder="Parent's email"
                type="email"
                value={form.parentEmail}
                onChange={(e) => setForm({ ...form, parentEmail: e.target.value })}
                error={errors.parentEmail}
              />
              <Field
                id="parentPhone"
                placeholder="Parent's phone number"
                inputMode="tel"
                value={form.parentPhone}
                onChange={(e) => setForm({ ...form, parentPhone: e.target.value })}
                error={errors.parentPhone}
              />
            </div>

            <h2 className="body-text">Referral Serial #</h2>
            <div className="grid1">
              <Field
                id="referral"
                placeholder="xxxxxxxxx"
                value={form.referral}
                onChange={(e) => setForm({ ...form, referral: e.target.value })}
                error={errors.referral}
              />
            </div>

            <div className="join-actions">
              <button className="btn secondary" type="submit">
                SUBMIT
              </button>
            </div>
          </form>
        </div>
      )}

      {/* STEP 3: THANK YOU MODAL */}
      {step === "done" && (
        <>
          <div className="join-modal">
            {/* confetti images */}
            <div className="go-back">
              <button
                onClick={resetPage}
              >
                X
              </button>
            </div>
            <img src={confettiImg} alt="" className="confetti-img" />

            <h2 id="joinThanksTitle" className="title-text blue">
              THANK YOU FOR JOINING
            </h2>
            <h2 className="title-text red">HASHEM’S ARMY</h2>

            <p className="thanks-text">
              Please check your email for the login to your family account where
              you can complete registration, log your accomplishments, earn
              medals, get promoted in rank and earn miles with which you can
              purchase tickets for incredible prizes in our auction.
            </p>
          </div>
        </>
      )}
    </div>
  );
};

export default Join;
