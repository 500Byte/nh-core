(function() {
    'use strict';

    function initDropdownWrappers() {
        const wrappers = document.querySelectorAll('.nh-dropdown-wrapper');
        wrappers.forEach(wrapper => {
            const trigger = wrapper.querySelector('.nh-dropdown-trigger');
            const content = wrapper.querySelector('.nh-dropdown-content');
            if (!trigger || !content) return;

            if (trigger.dataset.nhBound) return;
            trigger.dataset.nhBound = 'true';

            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isOpen = wrapper.classList.contains('nh-open');
                
                document.querySelectorAll('.nh-dropdown-wrapper').forEach(w => {
                    if (w !== wrapper) w.classList.remove('nh-open');
                });

                if (isOpen) {
                    wrapper.classList.remove('nh-open');
                } else {
                    wrapper.classList.add('nh-open');
                }
            });

            content.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    }

    document.addEventListener('click', function() {
        document.querySelectorAll('.nh-dropdown-wrapper').forEach(w => {
            w.classList.remove('nh-open');
        });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdownWrappers);
    } else {
        initDropdownWrappers();
    }

    window.addEventListener('nh-ajax-filtered', initDropdownWrappers);
})();
