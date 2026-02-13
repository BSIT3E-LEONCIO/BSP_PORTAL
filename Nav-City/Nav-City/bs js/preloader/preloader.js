document.addEventListener("DOMContentLoaded", function () {
    
    document.body.style.overflow = "hidden";

    setTimeout(function () {
        hidePreloader();
        
        document.body.style.overflow = "auto";
    }, 3000);

    function hidePreloader() {
        var preloader = document.getElementById("preloader");
        preloader.classList.add("hidden");
    }
});