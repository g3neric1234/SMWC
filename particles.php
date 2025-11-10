<?php
if (!defined('PARTICLES_BACKGROUND_INCLUDED')) {
    define('PARTICLES_BACKGROUND_INCLUDED', true);
?>
    <style>
        #particles-js {
            position: fixed;
            width: 100% !important;
            height: 100% !important;
            top: 0;
            left: 0;
            z-index: -1;
            background-color: #000000;
        }
        .particles-js-canvas-el {
            position: absolute !important;
            width: 100% !important;
            height: 100% !important;
        }
    </style>
    <div id="particles-js"></div>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        function initParticles() {
            particlesJS('particles-js', {
                "particles": {
                    "number": {
                        "value": 80,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#9333ea"
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        }
                    },
                    "opacity": {
                        "value": 0.5,
                        "random": false
                    },
                    "size": {
                        "value": 3,
                        "random": true
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#4f46e5",
                        "opacity": 0.4,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 2,
                        "direction": "none",
                        "random": false,
                        "straight": false,
                        "out_mode": "out"
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": true,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 140,
                            "line_linked": {
                                "opacity": 1
                            }
                        },
                        "push": {
                            "particles_nb": 4
                        }
                    }
                },
                "retina_detect": true
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Destruye cualquier instancia previa
            if (typeof pJSDom !== 'undefined' && pJSDom.length > 0) {
                pJSDom[0].pJS.fn.vendors.destroy();
            }
            
            // Inicializa particles.js
            initParticles();
            
            // Re-inicializa cuando hay parámetros en la URL
            if (window.location.search.includes('error=')) {
                setTimeout(initParticles, 300);
            }
        });

        window.addEventListener('resize', function() {
            if (typeof pJSDom !== 'undefined' && pJSDom.length > 0) {
                pJSDom[0].pJS.fn.vendors.destroy();
                initParticles();
            }
        });
    </script>
<?php
}