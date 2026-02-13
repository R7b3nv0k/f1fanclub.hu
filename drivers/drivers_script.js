// Scroll functionality for driver gallery
document.addEventListener('DOMContentLoaded', function() {
    const scrollLeftBtn = document.getElementById('scroll-left');
    const scrollRightBtn = document.getElementById('scroll-right');
    const driversContainer = document.querySelector('.drivers-container');
    const driversWrapper = document.getElementById('drivers-wrapper');
    
    // Scroll amount (adjust based on card width)
    const scrollAmount = 320; // Slightly more than card width for smooth scrolling
    
    // Left scroll button
    scrollLeftBtn.addEventListener('click', function() {
      driversContainer.scrollBy({
        left: -scrollAmount,
        behavior: 'smooth'
      });
    });
    
    // Right scroll button
    scrollRightBtn.addEventListener('click', function() {
      driversContainer.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
      });
    });
    
    // Optional: Add keyboard navigation
    document.addEventListener('keydown', function(event) {
      if (event.key === 'ArrowLeft') {
        driversContainer.scrollBy({
          left: -scrollAmount,
          behavior: 'smooth'
        });
      } else if (event.key === 'ArrowRight') {
        driversContainer.scrollBy({
          left: scrollAmount,
          behavior: 'smooth'
        });
      }
    });
    
    // Hide arrows when at edges (optional enhancement)
    function updateArrowVisibility() {
      const scrollLeft = driversContainer.scrollLeft;
      const maxScrollLeft = driversWrapper.scrollWidth - driversContainer.clientWidth;
      
      // Hide left arrow if at the beginning
      if (scrollLeft <= 10) {
        scrollLeftBtn.style.opacity = '0.3';
        scrollLeftBtn.style.cursor = 'default';
      } else {
        scrollLeftBtn.style.opacity = '0.8';
        scrollLeftBtn.style.cursor = 'pointer';
      }
      
      // Hide right arrow if at the end
      if (scrollLeft >= maxScrollLeft - 10) {
        scrollRightBtn.style.opacity = '0.3';
        scrollRightBtn.style.cursor = 'default';
      } else {
        scrollRightBtn.style.opacity = '0.8';
        scrollRightBtn.style.cursor = 'pointer';
      }
    }
    
    // Update arrow visibility on scroll
    driversContainer.addEventListener('scroll', updateArrowVisibility);
    
    // Initial check
    updateArrowVisibility();
    
    // Also update when window resizes
    window.addEventListener('resize', updateArrowVisibility);
});

// Driver statistics data - ALL 22 DRIVERS
const driverStats = {
    // ========== RED BULL ==========
    max_verstappen: {
      name: "MAX VERSTAPPEN",
      team: "ORACLE RED BULL RACING",
      nationality: "NETHERLANDS",
      flag: "🇳🇱",
      image: "kép/MAX_VERSTAPPEN.png",
      current: {
        position: "1st",
        points: "454",
        wins: "19",
        podiums: "21",
        poles: "12",
        fastestLaps: "9"
      },
      career: {
        races: "185",
        wins: "54",
        podiums: "98",
        poles: "33",
        fastestLaps: "28",
        titles: "3"
      }
    },
    
    isack_hadjar: {
      name: "ISACK HADJAR",
      team: "ORACLE RED BULL RACING",
      nationality: "FRANCE",
      flag: "🇫🇷",
      image: "kép/ISACK_HADJAR.png",
      current: {
        position: "20th",
        points: "0",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "0",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    // ========== FERRARI ==========
    lewis_hamilton: {
      name: "LEWIS HAMILTON",
      team: "SCUDERIA FERRARI",
      nationality: "UNITED KINGDOM",
      flag: "🇬🇧",
      image: "kép/LEWIS_HAMILTON.png",
      current: {
        position: "3rd",
        points: "234",
        wins: "0",
        podiums: "6",
        poles: "1",
        fastestLaps: "2"
      },
      career: {
        races: "332",
        wins: "103",
        podiums: "197",
        poles: "104",
        fastestLaps: "65",
        titles: "7"
      }
    },
    
    charles_leclerc: {
      name: "CHARLES LECLERC",
      team: "SCUDERIA FERRARI",
      nationality: "MONACO",
      flag: "🇲🇨",
      image: "kép/CHARLES_LECLERC.png",
      current: {
        position: "2nd",
        points: "308",
        wins: "3",
        podiums: "14",
        poles: "5",
        fastestLaps: "4"
      },
      career: {
        races: "125",
        wins: "7",
        podiums: "30",
        poles: "23",
        fastestLaps: "10",
        titles: "0"
      }
    },
    
    // ========== MERCEDES ==========
    kimi_antonelli: {
      name: "KIMI ANTONELLI",
      team: "MERCEDES-AMG PETRONAS",
      nationality: "ITALY",
      flag: "🇮🇹",
      image: "kép/KIMI_ANTONELLI.png",
      current: {
        position: "19th",
        points: "1",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "10",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    george_russell: {
      name: "GEORGE RUSSELL",
      team: "MERCEDES-AMG PETRONAS",
      nationality: "UNITED KINGDOM",
      flag: "🇬🇧",
      image: "kép/GEORGE_RUSSEL.png",
      current: {
        position: "4th",
        points: "198",
        wins: "1",
        podiums: "5",
        poles: "2",
        fastestLaps: "3"
      },
      career: {
        races: "104",
        wins: "2",
        podiums: "11",
        poles: "5",
        fastestLaps: "8",
        titles: "0"
      }
    },
    
    // ========== RACING BULLS ==========
    liam_lawson: {
      name: "LIAM LAWSON",
      team: "RACING BULLS",
      nationality: "NEW ZEALAND",
      flag: "🇳🇿",
      image: "kép/LIAM_LAWSON.png",
      current: {
        position: "15th",
        points: "12",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "5",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    arvid_lindblad: {
      name: "ARVID LINDBLAD",
      team: "RACING BULLS",
      nationality: "UNITED KINGDOM",
      flag: "🇬🇧",
      image: "kép/ARVID_LINDBLAD.png",
      current: {
        position: "N/A",
        points: "0",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "0",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    // ========== McLAREN ==========
    lando_norris: {
      name: "LANDO NORRIS",
      team: "McLAREN F1 TEAM",
      nationality: "UNITED KINGDOM",
      flag: "🇬🇧",
      image: "kép/LANDO_NORRIS.png",
      current: {
        position: "5th",
        points: "187",
        wins: "1",
        podiums: "8",
        poles: "2",
        fastestLaps: "4"
      },
      career: {
        races: "104",
        wins: "2",
        podiums: "15",
        poles: "3",
        fastestLaps: "8",
        titles: "0"
      }
    },
    
    oscar_piastri: {
      name: "OSCAR PIASTRI",
      team: "McLAREN F1 TEAM",
      nationality: "AUSTRALIA",
      flag: "🇦🇺",
      image: "kép/OSCAR_PIASTRI.png",
      current: {
        position: "6th",
        points: "156",
        wins: "0",
        podiums: "4",
        poles: "1",
        fastestLaps: "2"
      },
      career: {
        races: "42",
        wins: "0",
        podiums: "5",
        poles: "2",
        fastestLaps: "3",
        titles: "0"
      }
    },
    
    // ========== HAAS ==========
    esteban_ocon: {
      name: "ESTEBAN OCON",
      team: "MONEYGRAM HAAS F1 TEAM",
      nationality: "FRANCE",
      flag: "🇫🇷",
      image: "kép/ESTEBAN_OCON.png",
      current: {
        position: "14th",
        points: "24",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "133",
        wins: "1",
        podiums: "3",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    oliver_bearman: {
      name: "OLIVER BEARMAN",
      team: "MONEYGRAM HAAS F1 TEAM",
      nationality: "UNITED KINGDOM",
      flag: "🇬🇧",
      image: "kép/OLIVER_BEARMAN.png",
      current: {
        position: "17th",
        points: "6",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "2",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    // ========== CADILLAC ==========
    sergio_perez: {
      name: "SERGIO PEREZ",
      team: "CADILLAC RACING",
      nationality: "MEXICO",
      flag: "🇲🇽",
      image: "kép/SERGIO_PEREZ.png",
      current: {
        position: "8th",
        points: "107",
        wins: "0",
        podiums: "2",
        poles: "0",
        fastestLaps: "1"
      },
      career: {
        races: "263",
        wins: "6",
        podiums: "35",
        poles: "3",
        fastestLaps: "11",
        titles: "0"
      }
    },
    
    valtteri_bottas: {
      name: "VALTTERI BOTTAS",
      team: "CADILLAC RACING",
      nationality: "FINLAND",
      flag: "🇫🇮",
      image: "kép/VALTTERI_BOTTAS.png",
      current: {
        position: "16th",
        points: "8",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "223",
        wins: "10",
        podiums: "67",
        poles: "20",
        fastestLaps: "19",
        titles: "0"
      }
    },
    
    // ========== WILLIAMS ==========
    carlos_sainz: {
      name: "CARLOS SAINZ",
      team: "WILLIAMS RACING",
      nationality: "SPAIN",
      flag: "🇪🇸",
      image: "kép/CARLOS_SAINZ.png",
      current: {
        position: "7th",
        points: "124",
        wins: "1",
        podiums: "3",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "185",
        wins: "3",
        podiums: "19",
        poles: "5",
        fastestLaps: "3",
        titles: "0"
      }
    },
    
    alexander_albon: {
      name: "ALEXANDER ALBON",
      team: "WILLIAMS RACING",
      nationality: "THAILAND",
      flag: "🇹🇭",
      image: "kép/ALEXANDER_ALBON.png",
      current: {
        position: "12th",
        points: "32",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "83",
        wins: "0",
        podiums: "2",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    // ========== AUDI ==========
    nico_hulkenberg: {
      name: "NICO HÜLKENBERG",
      team: "AUDI F1 TEAM",
      nationality: "GERMANY",
      flag: "🇩🇪",
      image: "kép/NICO_HULKENBERG.png",
      current: {
        position: "13th",
        points: "28",
        wins: "0",
        podiums: "0",
        poles: "1",
        fastestLaps: "2"
      },
      career: {
        races: "211",
        wins: "0",
        podiums: "0",
        poles: "1",
        fastestLaps: "2",
        titles: "0"
      }
    },
    
    gabriel_bortoleto: {
      name: "GABRIEL BORTOLETO",
      team: "AUDI F1 TEAM",
      nationality: "BRAZIL",
      flag: "🇧🇷",
      image: "kép/GABRIEL_BORTOLETO.png",
      current: {
        position: "21st",
        points: "0",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "0",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    // ========== ASTON MARTIN ==========
    fernando_alonso: {
      name: "FERNANDO ALONSO",
      team: "ASTON MARTIN ARAMCO",
      nationality: "SPAIN",
      flag: "🇪🇸",
      image: "kép/FERNANDO_ALONSO.png",
      current: {
        position: "9th",
        points: "98",
        wins: "0",
        podiums: "1",
        poles: "0",
        fastestLaps: "1"
      },
      career: {
        races: "383",
        wins: "32",
        podiums: "106",
        poles: "22",
        fastestLaps: "24",
        titles: "2"
      }
    },
    
    lance_stroll: {
      name: "LANCE STROLL",
      team: "ASTON MARTIN ARAMCO",
      nationality: "CANADA",
      flag: "🇨🇦",
      image: "kép/LANCE_STROLL.png",
      current: {
        position: "10th",
        points: "56",
        wins: "0",
        podiums: "0",
        poles: "1",
        fastestLaps: "0"
      },
      career: {
        races: "147",
        wins: "0",
        podiums: "3",
        poles: "1",
        fastestLaps: "0",
        titles: "0"
      }
    },
    
    // ========== ALPINE ==========
    pierre_gasly: {
      name: "PIERRE GASLY",
      team: "ALPINE F1 TEAM",
      nationality: "FRANCE",
      flag: "🇫🇷",
      image: "kép/PIERRE_GASLY.png",
      current: {
        position: "11th",
        points: "45",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "1"
      },
      career: {
        races: "138",
        wins: "1",
        podiums: "4",
        poles: "0",
        fastestLaps: "3",
        titles: "0"
      }
    },
    
    franco_colapinto: {
      name: "FRANCO COLAPINTO",
      team: "ALPINE F1 TEAM",
      nationality: "ARGENTINA",
      flag: "🇦🇷",
      image: "kép/FRANCO_COLAPINTO.png",
      current: {
        position: "18th",
        points: "4",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0"
      },
      career: {
        races: "5",
        wins: "0",
        podiums: "0",
        poles: "0",
        fastestLaps: "0",
        titles: "0"
      }
    }
};
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
      e.stopPropagation(); // Prevent event from bubbling up
      driversContainer.scrollBy({
        left: -scrollAmount,
        behavior: 'smooth'
      });
    });
    
    // Right scroll button
    scrollRightBtn.addEventListener('click', function(e) {
      e.stopPropagation(); // Prevent event from bubbling up
      driversContainer.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
      });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
      if (event.key === 'ArrowLeft') {
        driversContainer.scrollBy({
          left: -scrollAmount,
          behavior: 'smooth'
        });
      } else if (event.key === 'ArrowRight') {
        driversContainer.scrollBy({
          left: scrollAmount,
          behavior: 'smooth'
        });
      } else if (event.key === 'Escape') {
        closeStatisticsPanel();
      }
    });
    
    // Update arrow visibility
    function updateArrowVisibility() {
      const scrollLeft = driversContainer.scrollLeft;
      const maxScrollLeft = driversWrapper.scrollWidth - driversContainer.clientWidth;
      
      if (scrollLeft <= 10) {
        scrollLeftBtn.style.opacity = '0.3';
        scrollLeftBtn.style.cursor = 'default';
      } else {
        scrollLeftBtn.style.opacity = '0.8';
        scrollLeftBtn.style.cursor = 'pointer';
      }
      
      if (scrollLeft >= maxScrollLeft - 10) {
        scrollRightBtn.style.opacity = '0.3';
        scrollRightBtn.style.cursor = 'default';
      } else {
        scrollRightBtn.style.opacity = '0.8';
        scrollRightBtn.style.cursor = 'pointer';
      }
    }
    
    // Track if we're in the process of switching drivers
    let isSwitchingDriver = false;
    
    function openStatisticsPanel(driverId, fromClick = true) {
      // If we're already switching drivers, ignore additional clicks
      if (isSwitchingDriver && fromClick) return;
      
      isSwitchingDriver = true;
      
      const stats = driverStats[driverId];
      if (!stats) {
        isSwitchingDriver = false;
        return;
      }
      
      // Remove any previously selected driver
      document.querySelectorAll('.driver-card').forEach(card => {
        card.classList.remove('selected');
      });
      
      // Highlight the clicked driver
      const selectedDriverCard = document.querySelector(`[data-driver="${driverId}"]`);
      if (selectedDriverCard) {
        selectedDriverCard.classList.add('selected');
        
        // Scroll to make the selected driver visible
        const container = document.querySelector('.drivers-container');
        const scrollLeft = selectedDriverCard.offsetLeft - (container.clientWidth / 2) + (selectedDriverCard.clientWidth / 2);
        
        container.scrollTo({
          left: scrollLeft,
          behavior: 'smooth'
        });
      }
      
      // Update panel content
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
      
      // Show panel and compress container
      statisticsPanel.classList.add('active');
      driversContainer.classList.add('panel-active');
      driversWrapper.classList.add('panel-active');
      scrollLeftBtn.classList.add('panel-active');
      scrollRightBtn.classList.add('panel-active');
      
      // Disable body scroll
      document.body.style.overflow = 'hidden';
      
      // Reset switching flag after a short delay
      setTimeout(() => {
        isSwitchingDriver = false;
      }, 300);
    }
    
    function closeStatisticsPanel() {
      // Remove selected highlight
      document.querySelectorAll('.driver-card').forEach(card => {
        card.classList.remove('selected');
      });
      
      statisticsPanel.classList.remove('active');
      driversContainer.classList.remove('panel-active');
      driversWrapper.classList.remove('panel-active');
      scrollLeftBtn.classList.remove('panel-active');
      scrollRightBtn.classList.remove('panel-active');
      
      // Enable body scroll
      document.body.style.overflow = '';
      
      // Reset switching flag
      isSwitchingDriver = false;
    }
    
    // Driver card click event - FIXED: Direct switching
    document.querySelectorAll('.driver-card').forEach(card => {
      card.addEventListener('click', function(e) {
        e.stopPropagation();
        const driverId = this.getAttribute('data-driver');
        
        // If panel is already open with a different driver, switch immediately
        if (statisticsPanel.classList.contains('active')) {
          const currentSelected = document.querySelector('.driver-card.selected');
          if (currentSelected && currentSelected.getAttribute('data-driver') !== driverId) {
            openStatisticsPanel(driverId);
            return;
          }
        }
        
        // Otherwise open/close as before
        if (statisticsPanel.classList.contains('active')) {
          closeStatisticsPanel();
        } else {
          openStatisticsPanel(driverId);
        }
      });
    });
    
    // Close panel button
    closePanelBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      closeStatisticsPanel();
    });
    
    // Update close panel when clicking outside - MORE PRECISE
    document.addEventListener('click', function(e) {
      // Check if click is on panel or its children
      const isPanelClick = statisticsPanel.contains(e.target);
      const isCloseButton = e.target === closePanelBtn || closePanelBtn.contains(e.target);
      const isDriverCard = e.target.closest('.driver-card');
      const isScrollButton = e.target.closest('.scroll-button');
      
      // Only close if:
      // 1. Panel is active
      // 2. NOT clicking on panel (except close button)
      // 3. NOT clicking on a driver card (handled separately)
      // 4. NOT clicking on scroll buttons
      if (statisticsPanel.classList.contains('active') && 
          !isPanelClick && 
          !isDriverCard && 
          !isScrollButton) {
        closeStatisticsPanel();
      }
    });
    
    // Stats toggle functionality
    toggleBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const period = this.getAttribute('data-period');
        
        // Update active button
        toggleBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Show/hide stats sections
        if (period === 'current') {
          document.getElementById('current-stats').style.display = 'grid';
          document.getElementById('career-stats').style.display = 'none';
        } else {
          document.getElementById('current-stats').style.display = 'none';
          document.getElementById('career-stats').style.display = 'grid';
        }
      });
    });
    
    // Update arrow visibility on scroll
    driversContainer.addEventListener('scroll', updateArrowVisibility);
    
    // Initial check
    updateArrowVisibility();
    
    // Update when window resizes
    window.addEventListener('resize', function() {
      updateArrowVisibility();
      
      // If panel is open, adjust the container width
      if (statisticsPanel.classList.contains('active')) {
        const panelWidth = statisticsPanel.offsetWidth;
        driversContainer.style.width = `calc(100% - ${panelWidth}px)`;
      }
    });
});