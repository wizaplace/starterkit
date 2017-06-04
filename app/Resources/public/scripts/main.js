import { SearchBar } from './templates/SearchBar';
import { SearchPage } from './templates/SearchPage';

// custom scripts
function onReady() {
    // add your own scripts here...
}

// wait until page is fully loaded
$(function() {

    // templates
    SearchBar.init();
    SearchPage.init();

    // trigger custom scripts
    onReady();
});
