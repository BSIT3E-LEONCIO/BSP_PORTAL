const storage = {
  set(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  },
  get(key, fallback = null) {
    const raw = localStorage.getItem(key);
    if (!raw) return fallback;
    try {
      return JSON.parse(raw);
    } catch (error) {
      return fallback;
    }
  }
};

const qs = (selector, scope = document) => scope.querySelector(selector);
const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

const initSignup = () => {
  const form = qs("#signup-form");
  if (!form || form.dataset.clientOnly !== "true") return;

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    if (!data.email || !data.password || !data.username) {
      alert("Please complete all fields.");
      return;
    }
    storage.set("bsp_signup", data);
    window.location.href = "step1.php";
  });
};

const initStep1 = () => {
  const form = qs("#step1-form");
  if (!form) return;

  const serveMainRadios = qsa("input[name='serve_main']", form);
  const serveSubRadios = qsa("input[name='serve_sub']", form);

  // Disable sub-options that don't match the selected leader type
  function updateSubOptions(activeType) {
    serveSubRadios.forEach(r => {
      if (!activeType) {
        r.disabled = false;
      } else if (r.dataset.main !== activeType) {
        r.disabled = true;
        r.checked = false;
      } else {
        r.disabled = false;
      }
    });
  }

  // When leader type radio is clicked, disable the other group's sub-options
  serveMainRadios.forEach(radio => {
    radio.addEventListener("change", () => updateSubOptions(radio.value));
  });

  // When a sub-option is clicked, auto-select the matching leader type
  serveSubRadios.forEach(radio => {
    radio.addEventListener("change", () => {
      const matchingMain = serveMainRadios.find(r => r.value === radio.dataset.main);
      if (matchingMain) matchingMain.checked = true;
      updateSubOptions(radio.dataset.main);
    });
  });

  // On page load, set correct state
  const initialMain = serveMainRadios.find(r => r.checked)?.value;
  updateSubOptions(initialMain);


  if (form.dataset.clientOnly === "true") {
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const data = Object.fromEntries(new FormData(form));

      if (!data.surname || !data.firstname || !data.mi || !data.sex || !data.civil_status || !data.tenure || !data.sponsoring || !data.council || !data.dob || !data.pob || !data.religion || !data.profession || !data.position) {
        alert("Please complete all required fields.");
        return;
      }
      if (!data.serve_sub) {
        alert("Select your service sub-option.");
        return;
      }
      storage.set("bsp_step1", data);
      window.location.href = "safe.php";
    });

    const saved = storage.get("bsp_step1");
    if (saved) {
      Object.entries(saved).forEach(([key, value]) => {
        const field = form.elements[key];
        if (!field) return;
        if (field.type === "radio") {
          const match = qs(`input[name='${key}'][value='${value}']`, form);
          if (match) match.checked = true;
        } else {
          field.value = value;
        }
      });
      if (saved.serve_main && serveMainInput) {
        serveMainInput.value = saved.serve_main;
      }
      if (saved.serve_sub && !saved.serve_main) {
        const matching = serveSubRadios.find((radio) => radio.value === saved.serve_sub);
        if (matching) setServeMainFromSub(matching);
      }
    }
  }

  if (form.dataset.clientOnly !== "true") {
    form.addEventListener("submit", (event) => {
      // Always set serve_main from checked serve_sub before validation
      const checkedSub = Array.from(form.elements['serve_sub']).find(r => r.checked);
      if (checkedSub && serveMainInput) {
        serveMainInput.value = checkedSub.dataset.main || "";
      }
      const formData = new FormData(form);
      if (!formData.get("surname") || !formData.get("firstname") || !formData.get("mi") || !formData.get("sex") || !formData.get("civil_status") || !formData.get("tenure") || !formData.get("sponsoring") || !formData.get("council") || !formData.get("dob") || !formData.get("pob") || !formData.get("religion") || !formData.get("profession") || !formData.get("position")) {
        alert("Please fill in all required fields.");
        event.preventDefault();
        return;
      }
      if (!formData.get("serve_sub")) {
        alert("Please choose your service sub-option.");
        event.preventDefault();
        return;
      }
      const summary = [
        `Name: ${formData.get("surname")}, ${formData.get("firstname")} ${formData.get("mi") || ""}`.trim(),
        `Sex: ${formData.get("sex") || ""}`,
        `Civil Status: ${formData.get("civil_status") || ""}`,
        `Tenure: ${formData.get("tenure") || ""}`,
        `Serve As: ${formData.get("serve_main") || ""} - ${formData.get("serve_sub") || ""}`,
        `Sponsoring Institutions: ${formData.get("sponsoring") || ""}`,
        `Council: ${formData.get("council") || ""}`,
        `Date of Birth: ${formData.get("dob") || ""}`,
        `Place of Birth: ${formData.get("pob") || ""}`,
        `Religion: ${formData.get("religion") || ""}`,
        `Profession: ${formData.get("profession") || ""}`,
        `Position/Title: ${formData.get("position") || ""}`
      ].join("\n");

      if (!window.confirm(`Please confirm your details:\n\n${summary}`)) {
        event.preventDefault();
      }
    });
  }
};

const initSafe = () => {
  const checkbox = qs("#safe-agree");
  const nextBtn = qs("#safe-next");
  if (!checkbox || !nextBtn) return;

  const toggle = () => {
    nextBtn.disabled = !checkbox.checked;
  };

  checkbox.addEventListener("change", toggle);
  toggle();
  nextBtn.addEventListener("click", () => {
    if (nextBtn.disabled) return;
    const target = nextBtn.dataset.href || "payment.php";
    window.location.href = target;
  });
};

const initPayment = () => {
  const uploadInput = qs("#payment-proof");
  const preview = qs("#upload-preview");
  const proceedBtn = qs("#payment-next");
  if (!uploadInput || !preview || !proceedBtn) return;

  uploadInput.addEventListener("change", () => {
    const file = uploadInput.files[0];
    if (!file) {
      preview.textContent = "Upload a payment proof image to continue.";
      proceedBtn.disabled = true;
      return;
    }
    preview.textContent = `Selected file: ${file.name}`;
    proceedBtn.disabled = false;
  });

  const form = qs("#payment-form");
  if (form) {
    form.addEventListener("submit", () => {
      storage.set("bsp_payment_time", Date.now());
    });
  }
};

const initWait = () => {
  const timer = qs("#timer");
  const message = qs("#timer-message");
  const loginBtn = qs("#timer-login");
  if (!timer || !message || !loginBtn) return;

  const startAttr = timer.dataset.startMs;
  const start = startAttr ? Number(startAttr) : storage.get("bsp_payment_time", Date.now());
  const total = 24 * 60 * 60 * 1000;

  const update = () => {
    const elapsed = Date.now() - start;
    const remaining = Math.max(total - elapsed, 0);

    const hours = Math.floor(remaining / (1000 * 60 * 60));
    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

    timer.textContent = `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;

    if (remaining === 0) {
      message.textContent = "Verification window completed. You may now return to login.";
      loginBtn.disabled = false;
      clearInterval(interval);
    }
  };

  loginBtn.addEventListener("click", () => {
    window.location.href = "logout.php";
  });

  loginBtn.disabled = true;
  update();
  const interval = setInterval(update, 1000);
};

const initLogin = () => {
  const loginForm = qs("#login-form");
  if (!loginForm || loginForm.dataset.clientOnly !== "true") return;

  loginForm.addEventListener("submit", (event) => {
    event.preventDefault();
    alert("Login is a UI-only prototype. Connect to the database to enable authentication.");
  });
};

initSignup();
initStep1();
initSafe();
initPayment();
initWait();
initLogin();
