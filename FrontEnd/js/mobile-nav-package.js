// Mobile Navigation for Package Detail Page
document.addEventListener('DOMContentLoaded', () => {
  const mobileToggle = document.querySelector(".mobile-menu-toggle");
  const mobileSidebar = document.querySelector(".mobile-sidebar");
  const sidebarOverlay = document.querySelector(".sidebar-overlay");

  if (mobileToggle && mobileSidebar && sidebarOverlay) {
    mobileToggle.addEventListener("click", function () {
      mobileSidebar.classList.toggle("active");
      sidebarOverlay.classList.toggle("active");
      document.body.classList.toggle("sidebar-open");
      mobileToggle.classList.toggle("active");
    });

    sidebarOverlay.addEventListener("click", function () {
      mobileSidebar.classList.remove("active");
      sidebarOverlay.classList.remove("active");
      document.body.classList.remove("sidebar-open");
      mobileToggle.classList.remove("active");
    });

    // Close sidebar when clicking outside
    document.addEventListener("click", function (e) {
      if (
        !mobileSidebar.contains(e.target) &&
        !mobileToggle.contains(e.target) &&
        mobileSidebar.classList.contains("active")
      ) {
        mobileSidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
        document.body.classList.remove("sidebar-open");
        mobileToggle.classList.remove("active");
      }
    });
  }

  // Header scroll effect
  const header = document.querySelector("header");
  if (header) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > 50) {
        header.classList.add("scrolled");
      } else {
        header.classList.remove("scrolled");
      }
    });
  }

  // Accordion functionality for itinerary days
  const dayHeaders = document.querySelectorAll(".day-header");
  dayHeaders.forEach((header) => {
    header.addEventListener("click", function () {
      const content = this.nextElementSibling;
      const isExpanded = content.classList.contains("expanded");

      // Close all other open sections
      document.querySelectorAll(".day-content").forEach((item) => {
        if (item !== content) {
          item.classList.remove("expanded");
          item.previousElementSibling.classList.remove("active");
        }
      });

      // Toggle current section
      this.classList.toggle("active");
      content.classList.toggle("expanded");

      // Smooth scroll to opened section
      if (!isExpanded) {
        setTimeout(() => {
          header.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest' 
          });
        }, 350);
      }
    });
  });
});
