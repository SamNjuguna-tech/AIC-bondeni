document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // CAROUSEL FUNCTIONALITY
    // ======================
    const initCarousel = () => {
        const carousel = document.getElementById('ministries-carousel');
        if (!carousel) return;

        const container = carousel.querySelector('.ministries-container');
        const items = carousel.querySelectorAll('.ministry-item');
        const itemHeight = 220; // Approximate height of each item with margin
        let scrollPosition = 0;
        let animationId;
        let isPaused = false;

        container.style.height = (items.length * itemHeight) + 'px';

        function scrollUp() {
            if (isPaused) return;

            scrollPosition += 0.5; // Slower scroll speed

            if (scrollPosition >= items.length / 2 * itemHeight) {
                scrollPosition = 0;
                container.style.transform = 'translateY(0)';
            } else {
                container.style.transform = `translateY(-${scrollPosition}px)`;
            }

            animationId = requestAnimationFrame(scrollUp);
        }

        scrollUp();

        // Pause on hover
        carousel.addEventListener('mouseenter', () => isPaused = true);
        carousel.addEventListener('mouseleave', () => {
            isPaused = false;
            scrollUp();
        });

        // Clean up animation when leaving page
        window.addEventListener('beforeunload', () => cancelAnimationFrame(animationId));
    };

    // =====================
    // SIDEBAR FUNCTIONALITY (UPDATED)
    // =====================
    const initSidebar = () => {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const logoutBtn = document.querySelector('.btn-toolbar .btn-group .btn-outline-danger');

        if (!sidebar || !sidebarToggle) return;

        // Handle logout button visibility
        const handleLogoutVisibility = () => {
            if (window.innerWidth <= 767.98) {
                logoutBtn?.classList.add('d-none');
                sidebarToggle.classList.remove('d-none');
            } else {
                logoutBtn?.classList.remove('d-none');
                sidebarToggle.classList.add('d-none');
                sidebar.classList.remove('show');
                if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
            }
        };

        // Initialize visibility
        handleLogoutVisibility();

        // Toggle sidebar function
        const toggleSidebar = () => {
            sidebar.classList.toggle('show');
            if (sidebarBackdrop) sidebarBackdrop.classList.toggle('show');
        };

        // Set up event listeners
        sidebarToggle.addEventListener('click', toggleSidebar);

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', toggleSidebar);
        }

        // Close sidebar when nav link is clicked (mobile only)
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 767.98) {
                    toggleSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', handleLogoutVisibility);
    };

    // =====================
    // ALERT FUNCTIONALITY
    // =====================
    const initAlerts = () => {
        window.setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    };

    // =====================
    // IMAGE PREVIEW FUNCTIONALITY
    // =====================
    const initImagePreviews = () => {
        // General image preview
        const previewImage = (input, previewId = 'imagePreview') => {
            const preview = input.closest('.card-body')?.querySelector(`#${previewId}`) || 
                          document.getElementById(previewId);
            if (!preview) return;

            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image img-thumbnail';
                    img.style.maxHeight = '150px';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        // Gallery image preview
        const galleryInput = document.getElementById('images');
        if (galleryInput) {
            galleryInput.addEventListener('change', function(e) {
                const preview = document.getElementById('preview');
                if (!preview) return;
                
                preview.innerHTML = '';
                
                for (const file of this.files) {
                    const reader = new FileReader();
                    const col = document.createElement('div');
                    col.className = 'col-md-3';
                    
                    reader.onload = function(e) {
                        col.innerHTML = `
                            <div class="card">
                                <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <p class="card-text small text-muted mb-0">${file.name}</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    reader.readAsDataURL(file);
                    preview.appendChild(col);
                }
            });
        }

        // Set up all image preview inputs
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                previewImage(this);
            });
        });
    };

    // =====================
    // JOIN FAMILY
    // =====================
        // Enhanced functionality
        document.addEventListener('DOMContentLoaded', function() {
            
            // Filter functionality
            const filterItems = document.querySelectorAll('[data-filter]');
            filterItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filter = this.getAttribute('data-filter');
                    const rows = document.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        if (filter === 'all') {
                            row.style.display = '';
                        } else {
                            const status = row.getAttribute('data-status');
                            row.style.display = status === filter ? '' : 'none';
                        }
                    });
                });
            });
            
            // Expand message on click
            const messageCells = document.querySelectorAll('.request-message');
            messageCells.forEach(cell => {
                cell.addEventListener('click', function() {
                    this.classList.toggle('expanded');
                });
            });
        });


    // =====================
    // FORM VALIDATION
    // =====================
    const initFormValidation = () => {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        // Set minimum date to today for date inputs
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.min = new Date().toISOString().split('T')[0];
        });
    };

    // =====================
    // SEARCH FUNCTIONALITY
    // =====================
    const initSearch = () => {
        // General table search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const input = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(input) ? '' : 'none';
                });
            });
        }

        // Events table search
        const searchEvents = document.getElementById('searchEvents');
        if (searchEvents) {
            searchEvents.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#eventsTable tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        
    };


    // =========================
    // MINISTRIES FUNCTIONS
    // =========================

    // Image preview functionality
    document.getElementById('image')?.addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail preview-image" style="max-height: 150px;">';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const input = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    });


    // =====================
    // TOOLTIPS
    // =====================
    const initTooltips = () => {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    };

    // =====================
    // INITIALIZE ALL
    // =====================
    initCarousel();
    initSidebar();
    initAlerts();
    initImagePreviews();
    initFormValidation();
    initSearch();
    initTooltips();
});