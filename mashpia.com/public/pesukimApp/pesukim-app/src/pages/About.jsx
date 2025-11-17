import React, { useRef, useState } from "react";
import "./About.css";


import rebbe from "../assets/images/rebbe.jpg";
import speaker from "../assets/images/sound-3d.svg";
import audioFile from "../assets/audio/test.mp3";

const About = () => {
  const audioRef = useRef(null);
  const [isPlaying, setIsPlaying] = useState(false);

  const toggleAudio = async () => {
    try {
      const audio = audioRef.current;
      if (!audio) return;
  
      if (audio.paused) {
        // Try to play
        try {
          await audio.play();
          setIsPlaying(true);
        } catch (error) {
          // Handle play errors (e.g., user interaction required, no audio source)
          console.error("Error playing audio:", error);
          setIsPlaying(false);
        }
      } else {
        // Pause if currently playing
        audio.pause();
        setIsPlaying(false);
      }
    } catch (error) {
      console.error("Error toggling audio:", error);
      setIsPlaying(false);
    }
  };

  // Keep state synced with audio events
  const handleEnded = () => setIsPlaying(false);
  const handlePlay = () => setIsPlaying(true);
  const handlePause = () => setIsPlaying(false);
  return (
    <div className="about-wrap">
      <article className="about-card">
        <header className="about-head">
          <h1 className="about-title">TO ALL BOYS AND GIRLS</h1>

          <div className="about-rt">
          <button
              className={`audio-btn ${isPlaying ? "playing" : ""}`}
              aria-label={isPlaying ? "Pause audio" : "Play audio"}
              onClick={toggleAudio}
            >
              <img src={speaker} alt="Speaker" />
            </button>
            {rebbe && (
              <img className="rebbe-img" src={rebbe} alt="The Rebbe" />
            )}
          </div>
        </header>

        <section className="about-body">
          <p className="body-text">Dear Children</p>

          <p className="body-text">
            The Lubavitcher Rebbe, Rabbi Menachem M. Schneerson, has requested <br/>
            that every effort should be made to mobilize “the Army of Hashem
            (G-d’s <br/> Army).” Children everywhere are being called upon to join
            this wonderful army.
          </p>

          <p className="body-text">
            You see, this Army is very special! You children are its soldiers and
            officers, and the commander-in-chief is G-d Himself. That’s why it’s
            called the army of Hashem.
          </p>

          <p className="body-text">
            By learning the Torah and keeping its Mitzvos, you are fighting the
            battle against the Yetzer Harah (the evil inclination) to bring peace
            and light into the world.
          </p>

          <p className="body-text">
            Surely you remember the stories of our great king David. He won many
            wars and conquered the enemies of the Jews. He wrote in the Book of
            Psalms that the strength of the Jewish people is founded upon the
            children. “From the mouths of babes and sucklings,” he wrote “G-d
            established strength to silence the enemy…” (Psalms 8:3). This means
            that when you boys and girls recite the words of Torah, you assure the
            peace and well-being of our people.
          </p>

          <p className="body-text">
            You can show the proper way to others too. Your family and friends can
            learn from you how beautiful it is to study Torah and observe its
            Mitzvos.
          </p>

          <p className="body-text">
            On this website, there are twelve passages from the Torah. If you
            study them by heart and teach them to your friends, you will be able
            to recite them everywhere. That’s how you can really help bring
            Moshiach Now!
          </p>
        </section>
      </article>
      {/* Uncomment this when the audio file is ready */}
      {/* <audio
          ref={audioRef}
          src={audioFile}
          preload="auto"
          onEnded={handleEnded}
          onPlay={handlePlay}
          onPause={handlePause}
        /> */}
    </div>
  );
};

export default About;
