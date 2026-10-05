document.addEventListener("DOMContentLoaded", () => {
    // Scroll Animation using Intersection Observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                // Optional: Stop observing once animated
                // observer.unobserve(entry.target); 
            }
        });
    }, {
        threshold: 0.1, // Element එකෙන් 10% ක් පෙනෙද්දී
        rootMargin: "0px 0px -50px 0px"
    });

    const hiddenElements = document.querySelectorAll('.fade-up');
    hiddenElements.forEach((el) => observer.observe(el));
});



