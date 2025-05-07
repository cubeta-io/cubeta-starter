(function () {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim();
    if (all) {
      return [...document.querySelectorAll(el)];
    } else {
      return document.querySelector(el);
    }
  };

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    if (all) {
      select(el, all).forEach((e) => e.addEventListener(type, listener));
    } else {
      select(el, all).addEventListener(type, listener);
    }
  };

  /**
   * Sidebar toggle
   */
  if (select(".toggle-sidebar-btn")) {
    on("click", ".toggle-sidebar-btn", function (e) {
      select("body").classList.toggle("toggle-sidebar");
    });
  }

  const getStoredTheme = () => localStorage.getItem("theme") ?? "light";
  const setStoredTheme = (theme) => localStorage.setItem("theme", theme);

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
      return storedTheme;
    }

    return window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  };

  const setTheme = (theme) => {
    if (theme === "auto") {
      document.documentElement.setAttribute(
        "data-bs-theme",
        window.matchMedia("(prefers-color-scheme: dark)").matches
          ? "dark"
          : "light",
      );
      setStoredTheme(theme);
    } else {
      document.documentElement.setAttribute("data-bs-theme", theme);
      setStoredTheme(theme);
    }
  };

  const theme = getPreferredTheme();
  setTheme(theme);

  on("click", ".theme-toggle", function (e) {
    const theme = getPreferredTheme();
    if (theme === "light") {
      setTheme("dark");
      e.target.classList.toggle("bi-sun-fill");
      e.target.classList.toggle("bi-moon-stars-fill");
    } else {
      setTheme("light");
      e.target.classList.toggle("bi-moon-stars-fill");
      e.target.classList.toggle("bi-sun-fill");
    }
  });
})();
