import { SearchClient } from '../components/SearchClient';

export class SearchPage {

    static init() {
        this.search = new SearchClient(Routing.generate('api_search_products'), '');
        this.urlParameters = this.search.restoreSearchFromUrl();

        this.bindVue();
    }

    static bindVue() {
        Vue.filter('round', function(value) {
            return Math.round(value);
        });

        let vm = new Vue({
            el: '#search-content',
            delimiters: ['${','}'],
            data: {
                loading: true,
                products: [],
                query: this.urlParameters.query || '',
                pagination: {
                    page: this.urlParameters.page || 1,
                    resultsPerPage: this.urlParameters.resultsPerPage || 12
                },

                // TODO: get filters from ajax in mounted method
                filters: this.urlParameters.filters,  // || {{ filters|default({})|json_encode|raw }},
                sorting: this.urlParameters.sorting || {},
                facets: [],
                currentUrl: window.location.href,
                geoFilter: this.urlParameters.geoFilter,
                calculatedPages: this.calculatePages,
        },
            
        methods: {
            refresh: function () {

                SearchPage.search.searchProducts(this.query, this.pagination.page, this.pagination.resultsPerPage, this.filters, this.sorting, this.geoFilter, {}, function (response) {
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

                    SearchPage.search.saveSearchInUrl(vm.query, vm.pagination.page, vm.pagination.resultsPerPage, vm.filters, vm.sorting, vm.geoFilter);

                    Vue.nextTick(function () {
                        // open category tree
                        vm.refreshCategoryTree();
                    });
                });
            },

            isChecked: function(facetName, variantName) {
                return this.filters.hasOwnProperty(facetName) && this.filters[facetName] == variantName;
            },

            setSort: function(criteria, direction) {
                if(criteria ===  null) {
                    this.sorting = null;
                } else {
                    this.sorting = {};
                    this.sorting[criteria] = direction;
                }
                this.refresh();
            },

            goToPage: function (page) {
                this.pagination.page = page;

                // got to page top
                let $container = $('html, body');
                let $ref = $("#search-content");
                $container.animate({ scrollTop: $ref.offset().top - $container.offset().top }, 'fast');

                this.refresh();
            },

            toggleFilter: function (facetName, variantName) {
                if (this.filters.hasOwnProperty(facetName) && this.filters[facetName] == variantName) {
                    // Clear filter
                    Vue.delete(this.filters, facetName);
                } else {
                    // Set filter
                    Vue.set(this.filters, facetName, variantName);
                }
                this.refresh();
            },

            changeResultsNumber: function (_resultsPerPage) {
                this.pagination.resultsPerPage = _resultsPerPage;
                this.refresh();
            },

            updateNumericFilter: function (facetName, min, max) {
                if (!this.filters[facetName]) {
                    this.filters[facetName] = {};
                }
                this.filters[facetName]['min'] = min;
                this.filters[facetName]['max'] = max;
                this.refresh();
            },

            clearFilters: function () {
                // Clear everything except the selected category
                for (let property in this.filters) {
                    if (property !== 'categories' && this.filters.hasOwnProperty(property)) {
                        Vue.delete(this.filters, property);
                    }
                }

                this.refresh();
            },

            imageStyle: function(product) {
                return {
                    backgroundImage: 'url(' + this.imageUrl(product) + ')',
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
            this.refresh();
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
