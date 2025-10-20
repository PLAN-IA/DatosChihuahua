// js/autocomplete.js - Funcionalidad de autocompletado

class AutoComplete {
    constructor(inputSelector, options = {}) {
        this.input = document.querySelector(inputSelector);
        this.options = {
            type: 'calles',
            maxResults: 10,
            minLength: 2,
            debounceDelay: 300,
            ...options
        };
        
        this.dropdown = null;
        this.isLoading = false;
        this.debounceTimer = null;
        
        this.init();
    }
    
    init() {
        if (!this.input) return;
        
        this.createDropdown();
        this.bindEvents();
    }
    
    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'autocomplete-dropdown';
        this.dropdown.style.display = 'none';
        
        // Insertar después del input
        this.input.parentNode.insertBefore(this.dropdown, this.input.nextSibling);
    }
    
    bindEvents() {
        this.input.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });
        
        this.input.addEventListener('focus', () => {
            if (this.input.value.length >= this.options.minLength) {
                this.showDropdown();
            }
        });
        
        this.input.addEventListener('blur', () => {
            // Delay para permitir clicks en el dropdown
            setTimeout(() => {
                this.hideDropdown();
            }, 200);
        });
        
        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
    }
    
    handleInput(value) {
        clearTimeout(this.debounceTimer);
        
        if (value.length < this.options.minLength) {
            this.hideDropdown();
            return;
        }
        
        this.debounceTimer = setTimeout(() => {
            this.search(value);
        }, this.options.debounceDelay);
    }
    
    async search(query) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const response = await fetch(`api/autocomplete.php?q=${encodeURIComponent(query)}&type=${this.options.type}&limit=${this.options.maxResults}`);
            const data = await response.json();
            
            this.displayResults(data.suggestions || []);
        } catch (error) {
            console.error('Error en autocompletado:', error);
            this.hideDropdown();
        } finally {
            this.isLoading = false;
        }
    }
    
    displayResults(suggestions) {
        if (suggestions.length === 0) {
            this.dropdown.innerHTML = '<div class="autocomplete-item no-results">No se encontraron resultados</div>';
        } else {
            this.dropdown.innerHTML = suggestions.map(suggestion => {
                let html = `<div class="autocomplete-item" data-value="${suggestion.value}">`;
                html += `<div class="autocomplete-label">${suggestion.label}</div>`;
                
                // Información adicional según el tipo
                if (this.options.type === 'calles' && suggestion.tipo) {
                    html += `<div class="autocomplete-info">${suggestion.tipo}</div>`;
                } else if (this.options.type === 'servicios' && suggestion.tipo) {
                    html += `<div class="autocomplete-info">${suggestion.tipo}</div>`;
                } else if (this.options.type === 'codigos_postales' && suggestion.municipio) {
                    html += `<div class="autocomplete-info">${suggestion.municipio}</div>`;
                }
                
                html += '</div>';
                return html;
            }).join('');
            
            this.bindItemEvents();
        }
        
        this.showDropdown();
    }
    
    bindItemEvents() {
        const items = this.dropdown.querySelectorAll('.autocomplete-item');
        items.forEach(item => {
            item.addEventListener('click', () => {
                const value = item.dataset.value;
                this.input.value = value;
                this.hideDropdown();
                this.input.dispatchEvent(new Event('change'));
            });
        });
    }
    
    showLoading() {
        this.dropdown.innerHTML = '<div class="autocomplete-item loading">Buscando...</div>';
        this.showDropdown();
    }
    
    showDropdown() {
        this.dropdown.style.display = 'block';
    }
    
    hideDropdown() {
        this.dropdown.style.display = 'none';
    }
    
    handleKeydown(e) {
        const items = this.dropdown.querySelectorAll('.autocomplete-item');
        const activeItem = this.dropdown.querySelector('.autocomplete-item.active');
        let activeIndex = Array.from(items).indexOf(activeItem);
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                this.setActiveItem(items[activeIndex]);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                this.setActiveItem(items[activeIndex]);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (activeItem) {
                    const value = activeItem.dataset.value;
                    this.input.value = value;
                    this.hideDropdown();
                    this.input.dispatchEvent(new Event('change'));
                }
                break;
                
            case 'Escape':
                this.hideDropdown();
                break;
        }
    }
    
    setActiveItem(item) {
        // Remover clase active de todos los items
        this.dropdown.querySelectorAll('.autocomplete-item').forEach(i => {
            i.classList.remove('active');
        });
        
        // Agregar clase active al item seleccionado
        if (item) {
            item.classList.add('active');
        }
    }
}

// Función para inicializar autocompletado fácilmente
function initAutoComplete(selector, options = {}) {
    return new AutoComplete(selector, options);
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar autocompletado para calles
    initAutoComplete('#buscar-calles', {
        type: 'calles',
        maxResults: 8
    });

    // Inicializar autocompletado para servicios
    initAutoComplete('#buscar-servicios', {
        type: 'servicios',
        maxResults: 8
    });

    // Inicializar autocompletado para códigos postales
    initAutoComplete('#buscar-codigo-postal', {
        type: 'codigos_postales',
        maxResults: 8
    });
});
