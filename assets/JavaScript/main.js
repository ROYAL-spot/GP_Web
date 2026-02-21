document.addEventListener("DOMContentLoaded", function () {

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

});

document.querySelector("form").addEventListener("submit", function() {

    localStorage.setItem("insuranceComplete", "true");

    // Check if ALL forms are completed
    if (
        localStorage.getItem("patientInfoComplete") === "true" &&
        localStorage.getItem("consentComplete") === "true" &&
        localStorage.getItem("insuranceComplete") === "true"
    ) {
        localStorage.setItem("newPatientCompleted", "true");
        window.location.href = "thank-you.html";
    }
});

  localStorage.removeItem("patientInfoComplete");
localStorage.removeItem("consentComplete");
localStorage.removeItem("insuranceComplete");
localStorage.removeItem("newPatientCompleted");
// Auto redirect after 5 seconds
setTimeout(function() {
    window.location.href = "index.html";
}, 5000);