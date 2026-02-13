var typed = new Typed("#element", {
    strings: [
        "Quality Scouts",
        "Inclusive Scout Activities",
        "Outdoor Scout Experiences",
        "Leadership Scout Training",
    ],
    typeSpeed: 60,
    backSpeed: 45,
    loop: true,
});

function openNav() {
    document.getElementById("myNav").style.height = "100%";
}

function closeNav() {
    document.getElementById("myNav").style.height = "0%";
}

AOS.init();
