import SearchClient from '../includes/search';

export default function header () {
    let search = new SearchClient(vars.header.apiBaseUrl + 'catalog/search/products', vars.header.apiBaseUrl + 'catalog/search/products/autocomplete');
    let urlParameters = search.restoreSearchFromUrl(); // used in Search page

    new Vue({
        el: '#searchbar',

        data: {
            selectedCategory: {
                id: vars.header.selectedCategoryId || "", // `vars` variable is declared in _search.html.twig
                name: vars.header.selectedCategoryName || vars.header.selectedCategoryAllCategories
            },
            productSuggestions: [],
            query: urlParameters.query || '',
            geoFilter: urlParameters.geoFilter || {},
        },

        methods: {
            selectCategory: function (categoryId, categoryName) {
                this.selectedCategory = {
                    id: categoryId,
                    name: categoryName
                };
            },

            // _.debounce is a function provided by lodash
            // to limit how often a particularly expensive operation can be run.
            productAutocomplete: _.debounce(function () {

                    let self = this;

                    search.autocomplete(this.query, function (results) {

                        // don't display suggestions if there is only one and that already matches user input
                        if (results.length === 1 && results[0].name === self.query) {
                            self.productSuggestions = [];
                            return;
                        }

                        // fill suggestions
                        self.productSuggestions = results;
                    });
                },
                // how long to wait for user to stop typing (in ms)
                200
            ),

            // watch user query input to display corresponding product suggestions (auto-completion)
            initProductAutocomplete: function () {
                let self = this;
                let $queryInput = $('[name="q"]');
                let $productSuggestions = $('.product-suggestions');

                // show suggestions when:
                // - user has typed at least 3 characters
                // - there are three characters and user clicks in the input field
                $queryInput.on('keyup click', function () {

                    if (self.query.length >= 3) {
                        $productSuggestions.removeClass('hidden');
                        self.productAutocomplete();

                    } else {
                        // don't fill/show suggestions if user input isn't specific enough
                        self.productSuggestions = [];
                    }
                });
            },

            initGoogleMap: function () {
                $(function() {
                    let labelInput = document.getElementById('geofilter-input');
                    this.googleMapsAutocomplete = new google.maps.places.Autocomplete(labelInput);
                    this.googleMapsAutocomplete.addListener('place_changed', this.geolocAutocompleted);
                });
            },

            // called when the user autocompletes a place with Google Maps
            geolocAutocompleted: function () {

                let place = this.googleMapsAutocomplete.getPlace();

                if (!place.geometry) {
                    // user entered a place that was not suggested and pressed Enter, or the request failed
                    this.geoFilter = {};
                    return;
                }

                this.geoFilter = {
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng(),
                    label: place.name
                };
            },
        },

        mounted: function () {

            // initialize the Google Maps autocomplete in search bar
            this.initGoogleMap();

            // input field behaviour
            this.initProductAutocomplete();
        }
    });
}
