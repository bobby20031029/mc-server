document.addEventListener("DOMContentLoaded", function() {
    const title = document.querySelector(".title");
    title.addEventListener("mouseover", function() {
        title.style.transform = "scale(1.1)";
        title.style.transition = "transform 0.3s ease-in-out";
    });
    title.addEventListener("mouseleave", function() {
        title.style.transform = "scale(1)";
    });
});
