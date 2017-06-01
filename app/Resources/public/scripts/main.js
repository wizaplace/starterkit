import { PriceSlider } from './components/price-slider';
import { SearchPage } from './pages/SearchPage';

// wait until page is fully loaded
$(function() {
    // components
    PriceSlider.init();

    // pages
    SearchPage.init();
});
