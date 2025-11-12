import { useEffect, useState } from "react";

// type Trackers = {
//   learnTeach: { goal: number; taught: number };
//   armyRecruitment: { goal: number; recruited: number };
//   pesukim: {
//     date: { dow: string; hebrew: string; gregorian: string };
//     today: number;
//     total: number;
//   };
// };

export function useTrackers(pollMs = 0) {
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  async function load() {
    try {
      setLoading(true);
      const res = await fetch("/api/pesukim/trackers.json", { cache: "no-store" });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      setData(await res.json());
      setError(null);
    } catch (e) {
      setError(e);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
    if (pollMs > 0) {
      const id = setInterval(load, pollMs);
      return () => clearInterval(id);
    }
  }, [pollMs]);

  return { data, error, loading, reload: load };
}
