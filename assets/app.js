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

// Import jQuery плагины
import './js/vendor/jquery-ui-1.8.13.slider.min.js';
import './js/vendor/detectmobilebrowser.js';
import './js/vendor/jquery.fancybox.min.js';
import './js/vendor/jQRangeSlider-min.js';

import * as Popper from '@popperjs/core';
window.Popper = Popper;

import tippy from 'tippy.js';
window.tippy = tippy;

import 'owl.carousel';

// Import main scripts
import './js/main.js';
// import './js/map-dealers.js';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
