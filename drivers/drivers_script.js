// Scroll functionality for driver gallery
document.addEventListener('DOMContentLoaded', function() {
    const scrollLeftBtn = document.getElementById('scroll-left');
    const scrollRightBtn = document.getElementById('scroll-right');
    const driversContainer = document.querySelector('.drivers-container');
    const driversWrapper = document.getElementById('drivers-wrapper');
    const statisticsPanel = document.getElementById('statistics-panel');
    const closePanelBtn = document.getElementById('close-panel');
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    
    // Scroll amount
    const scrollAmount = 840;
    
    // Left scroll button
    scrollLeftBtn.addEventListener('click', function(e) {
      e.stopPropagation(); 
      driversContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });
    
    // Right scroll button
    scrollRightBtn.addEventListener('click', function(e) {
      e.stopPropagation(); 
      driversContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
      if (event.key === 'ArrowLeft') {
        driversContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
      } else if (event.key === 'ArrowRight') {
        driversContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
      } else if (event.key === 'Escape') {
        closeStatisticsPanel();
      }
    });
    
    // Update arrow visibility
    function updateArrowVisibility() {
      const scrollLeft = driversContainer.scrollLeft;
      const maxScrollLeft = driversWrapper.scrollWidth - driversContainer.clientWidth;
      
      if (scrollLeft <= 10) {
        scrollLeftBtn.style.opacity = '0.3'; scrollLeftBtn.style.cursor = 'default';
      } else {
        scrollLeftBtn.style.opacity = '0.8'; scrollLeftBtn.style.cursor = 'pointer';
      }
      
      if (scrollLeft >= maxScrollLeft - 10) {
        scrollRightBtn.style.opacity = '0.3'; scrollRightBtn.style.cursor = 'default';
      } else {
        scrollRightBtn.style.opacity = '0.8'; scrollRightBtn.style.cursor = 'pointer';
      }
    }
    
    // Track if we're in the process of switching drivers
    let isSwitchingDriver = false;
    
    // WOW Factor: Itt veszi át a JS az adatokat a PHP-tól (Adatbázisból)!
    const driverStats = window.driverStatsFromDB || {};
    
    function openStatisticsPanel(driverId, fromClick = true) {
      if (isSwitchingDriver && fromClick) return;
      isSwitchingDriver = true;
      
      const stats = driverStats[driverId];
      if (!stats) {
        console.error("No stats found for driver:", driverId);
        isSwitchingDriver = false;
        return;
      }
      
      // Remove any previously selected driver
      document.querySelectorAll('.driver-card').forEach(card => card.classList.remove('selected'));
      
      // Highlight the clicked driver
      const selectedDriverCard = document.querySelector(`[data-driver="${driverId}"]`);
      if (selectedDriverCard) {
        selectedDriverCard.classList.add('selected');
        const container = document.querySelector('.drivers-container');
        const scrollLeft = selectedDriverCard.offsetLeft - (container.clientWidth / 2) + (selectedDriverCard.clientWidth / 2);
        container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
      }
      
      // Update panel content directly from Database Data
      document.getElementById('stats-driver-name').textContent = stats.name;
      document.getElementById('stats-driver-team').textContent = stats.team;
      document.getElementById('stats-nationality').textContent = stats.nationality;
      document.getElementById('stats-flag').textContent = stats.flag;
      document.getElementById('stats-driver-image').src = stats.image;
      document.getElementById('stats-driver-image').alt = stats.name;
      
      // Update current season stats
      document.getElementById('current-position').textContent = stats.current.position;
      document.getElementById('current-points').textContent = stats.current.points;
      document.getElementById('current-wins').textContent = stats.current.wins;
      document.getElementById('current-podiums').textContent = stats.current.podiums;
      document.getElementById('current-poles').textContent = stats.current.poles;
      document.getElementById('current-fastest-laps').textContent = stats.current.fastestLaps;
      
      // Update career stats
      document.getElementById('career-races').textContent = stats.career.races;
      document.getElementById('career-wins').textContent = stats.career.wins;
      document.getElementById('career-podiums').textContent = stats.career.podiums;
      document.getElementById('career-poles').textContent = stats.career.poles;
      document.getElementById('career-fastest-laps').textContent = stats.career.fastestLaps;
      document.getElementById('career-titles').textContent = stats.career.titles;
      
      statisticsPanel.classList.add('active');
      driversContainer.classList.add('panel-active');
      driversWrapper.classList.add('panel-active');
      scrollLeftBtn.classList.add('panel-active');
      scrollRightBtn.classList.add('panel-active');
      document.body.style.overflow = 'hidden';
      
      setTimeout(() => { isSwitchingDriver = false; }, 300);
    }
    
    function closeStatisticsPanel() {
      document.querySelectorAll('.driver-card').forEach(card => card.classList.remove('selected'));
      statisticsPanel.classList.remove('active');
      driversContainer.classList.remove('panel-active');
      driversWrapper.classList.remove('panel-active');
      scrollLeftBtn.classList.remove('panel-active');
      scrollRightBtn.classList.remove('panel-active');
      document.body.style.overflow = '';
      isSwitchingDriver = false;
    }
    
    // Driver card click event
    document.querySelectorAll('.driver-card').forEach(card => {
      card.addEventListener('click', function(e) {
        e.stopPropagation();
        const driverId = this.getAttribute('data-driver');
        
        if (statisticsPanel.classList.contains('active')) {
          const currentSelected = document.querySelector('.driver-card.selected');
          if (currentSelected && currentSelected.getAttribute('data-driver') !== driverId) {
            openStatisticsPanel(driverId);
            return;
          }
          closeStatisticsPanel();
        } else {
          openStatisticsPanel(driverId);
        }
      });
    });
    
    closePanelBtn.addEventListener('click', function(e) { e.stopPropagation(); closeStatisticsPanel(); });
    
    document.addEventListener('click', function(e) {
      const isPanelClick = statisticsPanel.contains(e.target);
      const isCloseButton = e.target === closePanelBtn || closePanelBtn.contains(e.target);
      const isDriverCard = e.target.closest('.driver-card');
      const isScrollButton = e.target.closest('.scroll-button');
      
      if (statisticsPanel.classList.contains('active') && !isPanelClick && !isDriverCard && !isScrollButton) {
        closeStatisticsPanel();
      }
    });
    
    // Stats toggle functionality
    toggleBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const period = this.getAttribute('data-period');
        toggleBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        if (period === 'current') {
          document.getElementById('current-stats').style.display = 'grid';
          document.getElementById('career-stats').style.display = 'none';
        } else {
          document.getElementById('current-stats').style.display = 'none';
          document.getElementById('career-stats').style.display = 'grid';
        }
      });
    });
    
    driversContainer.addEventListener('scroll', updateArrowVisibility);
    updateArrowVisibility();
    
    window.addEventListener('resize', function() {
      updateArrowVisibility();
      if (statisticsPanel.classList.contains('active')) {
        const panelWidth = statisticsPanel.offsetWidth;
        driversContainer.style.width = `calc(100% - ${panelWidth}px)`;
      }
    });
});