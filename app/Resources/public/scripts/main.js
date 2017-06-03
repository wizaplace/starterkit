import { SearchPage } from './pages/SearchPage';

// custom scripts
function onReady() {
    // add your own scripts here...
}

// wait until page is fully loaded
$(function() {
    // components
    PriceSlider.init();

    // pages
    SearchPage.init();



    // trigger custom scripts
    onReady();
});
