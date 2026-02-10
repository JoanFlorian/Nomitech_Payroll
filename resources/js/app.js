/**
 * Main Application Entry Point
 * Inicializa Alpine.js y registra componentes
 */

import './bootstrap';
import Alpine from 'alpinejs';

// Importar componentes
import searchableSelect from './components/searchableSelect';
import registerForm from './forms/registerForm';

// Exponer Alpine globalmente
window.Alpine = Alpine;

// Registrar componentes
Alpine.data('searchableSelect', searchableSelect);
Alpine.data('registerForm', registerForm);

// Iniciar Alpine
Alpine.start();
