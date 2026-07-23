(function() {
    function initPriceFilter() {
        const minInput = document.getElementById('nh-min-price');
        const maxInput = document.getElementById('nh-max-price');
        const submitBtn = document.getElementById('nh-submit-price-filter');
        const clearBtn = document.getElementById('nh-clear-price-filter');

        if (!submitBtn) return; // Evitar doble ejecución o errores si no está en el DOM

        const urlParams = new URLSearchParams(window.location.search);
        const currentMin = urlParams.get('min_price');
        const currentMax = urlParams.get('max_price');

        if (currentMin) minInput.value = currentMin;
        if (currentMax) maxInput.value = currentMax;
        if (currentMin || currentMax) {
            if (clearBtn) clearBtn.style.display = 'block';
        }

        // Función AJAX para cargar la página y reemplazar fragmentos del DOM
        function loadFilteredContent(url) {
            const targets = document.querySelectorAll('.elementor-widget-loop-grid, .elementor-widget-jet-listing-grid, .woocommerce-pagination, .jet-filters-pagination, .woocommerce-info');
            targets.forEach(t => t.style.opacity = '0.5');

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const selectors = [
                        '.elementor-widget-loop-grid', 
                        '.elementor-widget-jet-listing-grid', 
                        '.woocommerce-pagination', 
                        '.jet-filters-pagination',
                        '.woocommerce-info'
                    ];
                    
                    selectors.forEach(selector => {
                        const currentElements = document.querySelectorAll(selector);
                        const newElements = doc.querySelectorAll(selector);
                        
                        currentElements.forEach((el, index) => {
                            if (newElements[index]) {
                                el.innerHTML = newElements[index].innerHTML;
                            } else {
                                el.innerHTML = ''; // Limpiar si el elemento no existe en la nueva respuesta
                            }
                            el.style.opacity = '1';
                        });
                    });

                    // Actualizar URL en el historial
                    history.pushState(null, '', url);
                    
                    // Re-enlazar eventos en los nuevos elementos
                    bindPaginationLinks();
                })
                .catch(err => {
                    console.error('AJAX Filter error:', err);
                    targets.forEach(t => t.style.opacity = '1');
                });
        }

        function bindPaginationLinks() {
            const pageLinks = document.querySelectorAll('.woocommerce-pagination a, .jet-filters-pagination a');
            pageLinks.forEach(link => {
                // Evitar duplicar listeners
                link.removeEventListener('click', handlePaginationClick);
                link.addEventListener('click', handlePaginationClick);
            });
        }

        function handlePaginationClick(e) {
            e.preventDefault();
            loadFilteredContent(this.href);
        }

        bindPaginationLinks();

        submitBtn.addEventListener('click', function() {
            const minVal = minInput.value.trim();
            const maxVal = maxInput.value.trim();
            const currentUrl = new URL(window.location.href);
            
            if (minVal !== '' && parseInt(minVal) >= 0) {
                currentUrl.searchParams.set('min_price', minVal);
            } else {
                currentUrl.searchParams.delete('min_price');
            }
            
            if (maxVal !== '' && parseInt(maxVal) >= 0) {
                currentUrl.searchParams.set('max_price', maxVal);
            } else {
                currentUrl.searchParams.delete('max_price');
            }

            loadFilteredContent(currentUrl.toString());
            if (clearBtn) clearBtn.style.display = (minVal || maxVal) ? 'block' : 'none';
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                minInput.value = '';
                maxInput.value = '';
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.delete('min_price');
                currentUrl.searchParams.delete('max_price');

                loadFilteredContent(currentUrl.toString());
                clearBtn.style.display = 'none';
            });
        }
    }

    // Ejecutar inmediatamente si la página ya cargó (útil para inyecciones AJAX)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPriceFilter);
    } else {
        initPriceFilter();
    }
})();
