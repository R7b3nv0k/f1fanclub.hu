// Scroll functionality for team gallery
document.addEventListener('DOMContentLoaded', function() {
    const scrollLeftBtn = document.getElementById('scroll-left');
    const scrollRightBtn = document.getElementById('scroll-right');
    const teamsContainer = document.querySelector('.teams-container');
    const teamsWrapper = document.getElementById('teams-wrapper');
    const statisticsPanel = document.getElementById('statistics-panel');
    const closePanelBtn = document.getElementById('close-panel');
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    
    // Scroll amount
    const scrollAmount = 840;
    
    // Left scroll button
    scrollLeftBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        teamsContainer.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    });
    
    // Right scroll button
    scrollRightBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        teamsContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            teamsContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        } else if (event.key === 'ArrowRight') {
            teamsContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        } else if (event.key === 'Escape') {
            closeStatisticsPanel();
        }
    });
    
    // Update arrow visibility
    function updateArrowVisibility() {
        const scrollLeft = teamsContainer.scrollLeft;
        const maxScrollLeft = teamsWrapper.scrollWidth - teamsContainer.clientWidth;
        
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
    
    // Team statistics data
    const teamStats = {
        red_bull: {
            name: "RED BULL RACING",
            base: "MILTON KEYNES",
            country: "UNITED KINGDOM",
            flag: "ðŸ‡¬ðŸ‡§",
            image: "kÃ©p/RED_BULL_CAR.png",
            current: {
                position: "1st",
                points: "654",
                wins: "21",
                podiums: "32",
                poles: "14",
                fastestLaps: "12"
            },
            career: {
                races: "385",
                wins: "118",
                podiums: "278",
                poles: "103",
                fastestLaps: "98",
                titles: "6"
            }
        },
        
        ferrari: {
            name: "SCUDERIA FERRARI",
            base: "MARANELLO",
            country: "ITALY",
            flag: "ðŸ‡®ðŸ‡¹",
            image: "kÃ©p/FERRARI_CAR.png",
            current: {
                position: "2nd",
                points: "452",
                wins: "5",
                podiums: "18",
                poles: "6",
                fastestLaps: "7"
            },
            career: {
                races: "1080",
                wins: "245",
                podiums: "809",
                poles: "249",
                fastestLaps: "259",
                titles: "16"
            }
        },
        
        mercedes: {
            name: "MERCEDES-AMG",
            base: "BRACKLEY",
            country: "UNITED KINGDOM",
            flag: "ðŸ‡¬ðŸ‡§",
            image: "kÃ©p/MERCEDES_CAR.png",
            current: {
                position: "3rd",
                points: "354",
                wins: "3",
                podiums: "12",
                poles: "4",
                fastestLaps: "5"
            },
            career: {
                races: "295",
                wins: "125",
                podiums: "289",
                poles: "137",
                fastestLaps: "108",
                titles: "8"
            }
        },
        
        mclaren: {
            name: "McLAREN F1 TEAM",
            base: "WOKING",
            country: "UNITED KINGDOM",
            flag: "ðŸ‡¬ðŸ‡§",
            image: "kÃ©p/MCLAREN_CAR.png",
            current: {
                position: "4th",
                points: "302",
                wins: "2",
                podiums: "10",
                poles: "3",
                fastestLaps: "5"
            },
            career: {
                races: "945",
                wins: "183",
                podiums: "512",
                poles: "156",
                fastestLaps: "165",
                titles: "8"
            }
        },
        
        aston_martin: {
            name: "ASTON MARTIN",
            base: "SILVERSTONE",
            country: "UNITED KINGDOM",
            flag: "ðŸ‡¬ðŸ‡§",
            image: "kÃ©p/ASTON_MARTIN_CAR.png",
            current: {
                position: "5th",
                points: "156",
                wins: "0",
                podiums: "3",
                poles: "0",
                fastestLaps: "1"
            },
            career: {
                races: "95",
                wins: "0",
                podiums: "9",
                poles: "0",
                fastestLaps: "3",
                titles: "0"
            }
        },
        
        alpine: {
            name: "ALPINE F1 TEAM",
            base: "ENSTONE",
            country: "UNITED KINGDOM",
            flag: "ðŸ‡¬ðŸ‡§",
            image: "kÃ©p/ALPINE_CAR.png",
            current: {
                position: "6th",
                points: "98",
                wins: "0",
                podiums: "1",
                poles: "0",
                fastestLaps: "1"
            },
            career: {
                races: "145",
                wins: "1",
                podiums: "11",
                poles: "0",
                fastestLaps: "3",
                titles: "0"
            }
        },
        
        williams: {
            name: "WILLIAMS RACING",
            base: "GROVE",
            country: "UNITED KINGDOM",
            flag: "ðŸ‡¬ðŸ‡§",
            image: "kÃ©p/WILLIAMS_CAR.png",
            current: {
                position: "7th",
                points: "67",
                wins: "0",
                podiums: "0",
                poles: "0",
                fastestLaps: "0"
            },
            career: {
                races: "815",
                wins: "114",
                podiums: "313",
                poles: "128",
                fastestLaps: "133",
                titles: "9"
            }
        },
        
        racing_bulls: {
            name: "RACING BULLS",
            base: "FAENZA",
            country: "ITALY",
            flag: "ðŸ‡®ðŸ‡¹",
            image: "kÃ©p/RACING_BULLS_CAR.png",
            current: {
                position: "8th",
                points: "45",
                wins: "0",
                podiums: "0",
                poles: "0",
                fastestLaps: "0"
            },
            career: {
                races: "165",
                wins: "0",
                podiums: "2",
                poles: "0",
                fastestLaps: "0",
                titles: "0"
            }
        },
        
        haas: {
            name: "MONEYGRAM HAAS",
            base: "KANNAPOLIS",
            country: "UNITED STATES",
            flag: "ðŸ‡ºðŸ‡¸",
            image: "kÃ©p/HAAS_CAR.png",
            current: {
                position: "9th",
                points: "34",
                wins: "0",
                podiums: "0",
                poles: "0",
                fastestLaps: "0"
            },
            career: {
                races: "176",
                wins: "0",
                podiums: "0",
                poles: "0",
                fastestLaps: "0",
                titles: "0"
            }
        },
        
        audi: {
            name: "AUDI F1 TEAM",
            base: "HINWIL",
            country: "SWITZERLAND",
            flag: "ðŸ‡¨ðŸ‡­",
            image: "kÃ©p/AUDI_CAR.png",
            current: {
                position: "10th",
                points: "12",
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
        
        cadillac: {
            name: "CADILLAC RACING",
            base: "CHARLOTTE",
            country: "UNITED STATES",
            flag: "ðŸ‡ºðŸ‡¸",
            image: "kÃ©p/CADILLAC_CAR.png",
            current: {
                position: "11th",
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
        }
    };
    
    let isSwitchingTeam = false;
    
    function openStatisticsPanel(teamId, fromClick = true) {
        if (isSwitchingTeam && fromClick) return;
        
        isSwitchingTeam = true;
        
        const stats = teamStats[teamId];
        if (!stats) {
            isSwitchingTeam = false;
            return;
        }
        
        // Remove any previously selected team
        document.querySelectorAll('.team-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Highlight the clicked team
        const selectedTeamCard = document.querySelector(`[data-team-id="${teamId}"]`);
        if (selectedTeamCard) {
            selectedTeamCard.classList.add('selected');
            
            // Scroll to make the selected team visible
            const container = document.querySelector('.teams-container');
            const scrollLeft = selectedTeamCard.offsetLeft - (container.clientWidth / 2) + (selectedTeamCard.clientWidth / 2);
            
            container.scrollTo({
                left: scrollLeft,
                behavior: 'smooth'
            });
        }
        
        // Update panel content
        document.getElementById('stats-team-name').textContent = stats.name;
        document.getElementById('stats-team-base').textContent = stats.base;
        document.getElementById('stats-country').textContent = stats.country;
        document.getElementById('stats-flag').textContent = stats.flag;
        document.getElementById('stats-team-image').src = stats.image;
        document.getElementById('stats-team-image').alt = stats.name;
        
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
        teamsContainer.classList.add('panel-active');
        teamsWrapper.classList.add('panel-active');
        scrollLeftBtn.classList.add('panel-active');
        scrollRightBtn.classList.add('panel-active');
        
        // Disable body scroll and add class
        document.body.style.overflow = 'hidden';
        document.body.classList.add('panel-active');
        
        setTimeout(() => {
            isSwitchingTeam = false;
        }, 300);
    }
    
    function closeStatisticsPanel() {
        document.querySelectorAll('.team-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        statisticsPanel.classList.remove('active');
        teamsContainer.classList.remove('panel-active');
        teamsWrapper.classList.remove('panel-active');
        scrollLeftBtn.classList.remove('panel-active');
        scrollRightBtn.classList.remove('panel-active');
        
        // Re-enable body scroll and remove class
        document.body.style.overflow = '';
        document.body.classList.remove('panel-active');
        isSwitchingTeam = false;
    }
    
    // Team card click event
    document.querySelectorAll('.team-card').forEach(card => {
        card.addEventListener('click', function(e) {
            e.stopPropagation();
            const teamId = this.getAttribute('data-team-id');
            
            if (statisticsPanel.classList.contains('active')) {
                const currentSelected = document.querySelector('.team-card.selected');
                if (currentSelected && currentSelected.getAttribute('data-team-id') !== teamId) {
                    openStatisticsPanel(teamId);
                    return;
                }
            }
            
            if (statisticsPanel.classList.contains('active')) {
                closeStatisticsPanel();
            } else {
                openStatisticsPanel(teamId);
            }
        });
    });
    
    // Close panel button
    closePanelBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        closeStatisticsPanel();
    });
    
    // Update close panel when clicking outside
    document.addEventListener('click', function(e) {
        const isPanelClick = statisticsPanel.contains(e.target);
        const isCloseButton = e.target === closePanelBtn || closePanelBtn.contains(e.target);
        const isTeamCard = e.target.closest('.team-card');
        const isScrollButton = e.target.closest('.scroll-button');
        
        if (statisticsPanel.classList.contains('active') && 
            !isPanelClick && 
            !isTeamCard && 
            !isScrollButton) {
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
    
    teamsContainer.addEventListener('scroll', updateArrowVisibility);
    
    updateArrowVisibility();
    
    window.addEventListener('resize', function() {
        updateArrowVisibility();
        
        if (statisticsPanel.classList.contains('active')) {
            const panelWidth = statisticsPanel.offsetWidth;
            teamsContainer.style.width = `calc(100% - ${panelWidth}px)`;
        }
    });
    function updateStatsPanel(teamCard) {
    // Csapat adatok lekérése a kártyáról
    const teamLogo = getTeamLogoFilename(teamCard);
    const teamNameElem = teamCard.querySelector('.team-name');
    const teamPrincipalElem = teamCard.querySelector('.team-principal');
    const teamDetailsElem = teamCard.querySelector('.team-details');
    
    // Csapat adatok kinyerése
    let fullTeamName = teamNameElem ? teamNameElem.textContent : 'CSAPAT NÉV';
    let teamBase = teamPrincipalElem ? teamPrincipalElem.textContent : 'SZÉKHELY';
    let flagText = '';
    let countryText = '';
    
    if (teamDetailsElem) {
        // A zászló emoji lekérése - pontosabb módszer
        const flagSpan = teamDetailsElem.querySelector('.flag');
        if (flagSpan) {
            flagText = flagSpan.textContent.trim();
        }
        // Az ország szöveg lekérése - a zászló utáni szöveg
        const detailsText = teamDetailsElem.cloneNode(true);
        const flagSpanClone = detailsText.querySelector('.flag');
        if (flagSpanClone) {
            flagSpanClone.remove();
        }
        countryText = detailsText.textContent.trim();
    }
    
    // Alap csapat adatok frissítése
    if (statsTeamName) statsTeamName.textContent = fullTeamName;
    if (statsTeamBase) statsTeamBase.textContent = teamBase;
    if (statsFlag) statsFlag.textContent = flagText || '🏁';
    if (statsCountry) statsCountry.textContent = countryText || 'ORSZÁG';
    
    // Csapat logó frissítése
    if (statsTeamImage) {
        if (teamLogo) {
            statsTeamImage.src = teamLogo;
            statsTeamImage.alt = `${fullTeamName} logó`;
            statsTeamImage.style.display = 'block';
        } else {
            statsTeamImage.style.display = 'none';
        }
    }
}
});