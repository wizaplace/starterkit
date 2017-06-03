import { SearchClient } from '../components/SearchClient';
import { PriceSlider } from '../components/PriceSlider';

// helpers
const searchContainerId = "search";
const searchPageIsLoaded = $(`#${searchContainerId}`).length;
let instance = null; // will hold a reference to this class as a singleton

export class SearchPage {

    constructor() {

        if(! instance) {

            instance = this; // set singleton

            this.search = new SearchClient(Routing.generate('api_search_products'), '');
            this.urlParameters = this.search.restoreSearchFromUrl();

            this.priceSliderInit();
            this.searchInit();
        }

        return instance;
    }

    static init() {

        // code is executed only on search page
        if(searchPageIsLoaded) {
            return new this; // return singleton instance of this class
        }
    }

    // price slider filter
    priceSliderInit() {

    }

    // search related functions (filters, facets, pagination, etc.)
    searchInit() {

        Vue.filter('round', function(value) {
            return Math.round(value);
        });

        // as 'this' here represents the Vue object, 'instance' is used to reference the SearchPage class
        // and to avoid confusion about the 'this' keyword, 'vm' is used to reference the current Vue object
        let vm = new Vue({
            el: `#${searchContainerId}`,
            delimiters: ['${','}'], // avoid conflict with twig '{{ }}' syntax
            data: {
                loading: true,
                products: [],
                query: vm.urlParameters.query || '',
                pagination: {
                    page: vm.urlParameters.page || 1,
                    resultsPerPage: vm.urlParameters.resultsPerPage || 12
                },

                // TODO: get filters from ajax in mounted method
                filters: vm.urlParameters.filters,  // || {{ filters|default({})|json_encode|raw }},
                sorting: vm.urlParameters.sorting || {},
                facets: [],
                currentUrl: window.location.href,
                geoFilter: vm.urlParameters.geoFilter,
                calculatedPages: vm.calculatePages,
        },
            
        methods: {
            refresh: function () {

                instance.search.searchProducts(vm.query, vm.pagination.page, vm.pagination.resultsPerPage, vm.filters, vm.sorting, vm.geoFilter, {}, function (response) {

                    vm.loading = false;
                    vm.products = response.results;
                    vm.pagination = response.pagination;

                    // filter returned facets
                    Object.keys(response.facets).map(function (attributeId) {
                        let variants = response.facets[attributeId];
                        Object.keys(variants).map(function (variantId) {

                            // remove reverse attribute this.search (∅)
                            if (variantId === 'values') {
                                for (let valueId in response.facets[attributeId][variantId]) {

                                    // remove ∅ variant
                                    if (valueId === '∅') {
                                        delete response.facets[attributeId][variantId][valueId];
                                    }
                                }
                            }
                        });
                    });

                    vm.facets = response.facets;

                    instance.search.saveSearchInUrl(vm.query, vm.pagination.page, vm.pagination.resultsPerPage, vm.filters, vm.sorting, vm.geoFilter);

                    Vue.nextTick(function () {
                        // open category tree
                        vm.refreshCategoryTree();
                    });
                });
            },

            setSort: function(criteria, direction) {
                if(criteria ===  null) {
                    vm.sorting = null;
                } else {
                    vm.sorting = {};
                    vm.sorting[criteria] = direction;
                }
                vm.refresh();
            },

            goToPage: function (page) {
                vm.pagination.page = page;

                // got to page top
                let $container = $('html, body');
                let $ref = $("#search-content");
                $container.animate({ scrollTop: $ref.offset().top - $container.offset().top }, 'fast');

                vm.refresh();
            },

            toggleFilter: function (facetName, variantName) {
                if (vm.filters.hasOwnProperty(facetName) && vm.filters[facetName] == variantName) {
                    // Clear filter
                    Vue.delete(vm.filters, facetName);
                } else {
                    // Set filter
                    Vue.set(vm.filters, facetName, variantName);
                }
                vm.refresh();
            },

            changeResultsNumber: function (_resultsPerPage) {
                vm.pagination.resultsPerPage = _resultsPerPage;
                vm.refresh();
            },

            updateNumericFilter: function (facetName, min, max) {
                if ( ! vm.filters[facetName]) {
                    vm.filters[facetName] = {};
                }
                vm.filters[facetName]['min'] = min;
                vm.filters[facetName]['max'] = max;
                vm.refresh();
            },

            clearFilters: function () {
                // Clear everything except the selected category
                for (let property in this.filters) {
                    if (property !== 'categories' && vm.filters.hasOwnProperty(property)) {
                        Vue.delete(vm.filters, property);
                    }
                }

                vm.refresh();
            },

            imageStyle: function(product) {
                return {
                    backgroundImage: 'url(' + vm.imageUrl(product) + ')',
                    backgroundRepeat: 'no-repeat',
                    backgroundPosition: 'center',
                    backgroundSize: 'cover',
                }
            },

            imageUrl: function (product) {
                if(product.mainImage != null) {
                    return "{{ apiUrl }}/image/" + product.mainImage.id + "?w=500&h=500";
                } else {
                    return "{{ asset('images/legacy/no-image.jpg') }}"
                }
            },

            getDiscount: function(product) {
                if(! product.crossedOutPrice) { return }

                let oldPrice = product.crossedOutPrice;
                let newPrice = product.minimumPrice;
                let discount = ((newPrice - oldPrice) * 100 / oldPrice);
                return Math.round(discount);
            },

            isNewProduct: function (product) {
                let aWeekAgo = moment().subtract(7, 'days');
                return moment.unix(product.createdAt).isAfter(aWeekAgo);
            },

            refreshCategoryTree: function() {
                let $selectedCategory = $('#categories .selected-category');

                // open category tree
                $selectedCategory.parents('#categories .collapse').addClass('in');

                // cleanup
                $('#categories .selected-category').removeClass('selected-category');
                $('#categories .selected').removeClass('selected');

                // ...and select the right ones
                $selectedCategory.addClass('selected-category');
                $selectedCategory.find(".category-name").first().addClass('selected');
                $selectedCategory.parents('#categories .category').children(".category-name").addClass('selected');

                // switch root category plus/minus icon
                let rootCategoryIcon = $selectedCategory.parents('#categories .category').find(".glyphicon-plus");
                rootCategoryIcon.toggleClass("glyphicon-plus glyphicon-minus");
            },

            isCurrentCategory: function(categoryId) {
                // return this.filters['categories'] == categoryId;
            },

            submitBasket: function(declinationId) {

                $.ajax({
                    type: "POST",
                    url: "{{ path('basket_add_item') }}",
                    data: { declinationId: declinationId, quantity: 1 },
                    success: function(response) {
                        hydrateModal(response.addedProduct, response.message);
                    }
                });
            },

            range: function (a, b) {
                let rangeArray = [];

                for(let i = a; i < b; i++) {
                    rangeArray.push(i);
                }

                return rangeArray;
            },
        },

        mounted: function () {
            // Trigger the first refresh
            vm.refresh();
        },

        filters: {
            price: W.formatPrice
        }
    });

        // price slider
        // Vue.component('slider', {
        //     template: '#slider-template',
        //     props: ['min', 'max'],
        //     data: function () {
        //         return {
        //             currentMin: this.min,
        //             currentMax: this.max
        //         }
        //     },
        //     activated: function () {
        //         let view = this;
        //
        //         $('#facet-slider').slider({
        //             range: true,
        //             min: Math.floor(view.min),
        //             max: Math.ceil(view.max),
        //             values: [view.currentMin, view.currentMax],
        //             slide: function (event, ui) {
        //                 view.currentMin = ui.values[0];
        //                 view.currentMax = ui.values[1];
        //             },
        //             stop: function () {
        //                 // Dispatch an event to the main view to update the this.search
        //                 view.$emit('update', view.currentMin, view.currentMax);
        //             }
        //         });
        //     }
        // });
    }
}
