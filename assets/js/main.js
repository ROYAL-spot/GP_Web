document.addEventListener("DOMContentLoaded", function () {

    /* ==============================
       MOBILE MENU
    ============================== */

    const hamburger = document.getElementById("hamburger");
    const mobileMenu = document.getElementById("mobileMenu");
    const closeMenu = document.getElementById("closeMenu");
    const body = document.body;

    if (hamburger && mobileMenu && closeMenu) {

        function openMenu() {
            mobileMenu.classList.add("active");
            body.style.overflow = "hidden";
        }

        function closeMobileMenu() {
            mobileMenu.classList.remove("active");
            body.style.overflow = "auto";
        }

        hamburger.addEventListener("click", openMenu);
        closeMenu.addEventListener("click", closeMobileMenu);

        document.addEventListener("click", function (e) {
            if (
                mobileMenu.classList.contains("active") &&
                !mobileMenu.contains(e.target) &&
                !hamburger.contains(e.target)
            ) {
                closeMobileMenu();
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                closeMobileMenu();
            }
        });
    }


    /* ==============================
       BOOKING PAGE - PREVENT PAST DATES
    ============================== */

    const dateInput = document.getElementById("appointment_date");
    if (dateInput) {
        dateInput.min = new Date().toISOString().split("T")[0];
    }

/* ==============================
   PATIENT PORTAL MULTI-STEP FORM
============================== */

const multiForm = document.getElementById("multiForm");

if (multiForm) {

    let currentStep = 0;
    const steps = multiForm.querySelectorAll(".step");
    const progress = document.getElementById("progressFill");

    function updateSteps() {

    steps.forEach((step, index) => {
        step.classList.toggle("active", index === currentStep);
    });

    if (progress) {
        progress.style.width =
            ((currentStep + 1) / steps.length) * 100 + "%";
    }
}

    multiForm.addEventListener("click", function (e) {

        if (e.target.matches("[data-next]")) {

            const currentInputs =
                steps[currentStep].querySelectorAll("input, textarea, select");

            for (let input of currentInputs) {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    return;
                }
            }

            if (currentStep < steps.length - 1) {
                currentStep++;
                updateSteps();
            }
        }

        if (e.target.matches("[data-prev]")) {
            if (currentStep > 0) {
                currentStep--;
                updateSteps();
            }
        }

    });

    updateSteps();
}

    /* ==============================
       THANK YOU PAGE AUTO REDIRECT
    ============================== */

    if (window.location.pathname.includes("thank-you.html")) {

        setTimeout(function () {
            window.location.href = "index.html";
        }, 5000);
    }

});