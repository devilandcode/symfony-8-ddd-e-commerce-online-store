import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import $ from 'jquery';
window.$ = window.jQuery = $;

// Импортируем jQuery плагины
import './js/vendor/detectmobilebrowser.js';
import './js/vendor/jquery.fancybox.min.js';

import 'owl.carousel';

// Импортируем кастомные JS файлы
import './js/main.js';
// import './js/map-dealers.js';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
