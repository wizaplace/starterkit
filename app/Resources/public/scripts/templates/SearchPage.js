import { SearchClient } from '../components/SearchClient';
import { PriceSlider } from '../components/PriceSlider';

const templateContainerId = "search";
const containerIsLoaded = $(`#${templateContainerId}`).length;
const newProductLifeSpan = 7; // number of days a product is considered as 'new'

let instance = null; // will hold a reference to this class as a singleton

export class SearchPage {

    constructor() {

        if( ! instance ) {

            instance = this; // set singleton

            this.search = new SearchClient(Routing.generate('api_search'), '');
            this.urlParameters = this.search.restoreSearchFromUrl();

            this.priceSliderInit();
            this.searchInit();
        }

        return instance;
    }

    static init() {

        // code is executed only on this template
        if( containerIsLoaded ) {
            return new this; // return singleton instance of this class
        }
    }

    // price slider filter
    priceSliderInit() {

    }

    // search related functions (filters, facets, pagination, etc.) and search product data
    searchInit() {

        Vue.filter('round', function(value) {
            return Math.round(value);
        });

        // as 'this' here represents the Vue object, 'instance' is used to reference the SearchPage class
        new Vue({
            el: `#${templateContainerId}`,

            delimiters: ['${','}'], // avoid conflict with twig '{{ }}' syntax

            data: {
                products: [], // currently filtered list of products to display
                facets: [], // TODO comment

                // extracted from URL
                query: instance.urlParameters.query || '', // term used in search-bar
                pagination: {
                    currentPage: instance.urlParameters.currentPage || 1,
                    resultsPerPage: instance.urlParameters.resultsPerPage || 12
                },
                filters: instance.urlParameters.filters || {},
                sorting: instance.urlParameters.sorting || {}, // sorting.name: 'asc' or 'desc'
                geoFilter: instance.urlParameters.geoFilter,
            },

            methods: {

                // refresh to update page state
                refresh() {

                    instance.search.searchProducts(
                        this.query,
                        this.pagination.currentPage,
                        this.pagination.resultsPerPage,
                        this.filters,
                        this.sorting,
                        this.geoFilter,
                        {},
                        (response) => {

                            this.products = response.results;

                            // pagination data
                            this.pagination = response.pagination;

                            // filter returned facets
                            Object.keys(response.facets).map(function (attributeId) {
                                let variants = response.facets[attributeId];
                                Object.keys(variants).map(function (variantId) {

                                    // remove reverse attribute instance.search (∅)
                                    if(variantId === 'values') {
                                        for (let valueId in response.facets[attributeId][variantId]) {

                                            // remove ∅ variant
                                            if(valueId === '∅') {
                                                delete response.facets[attributeId][variantId][valueId];
                                            }
                                        }
                                    }
                                });
                            });

                            // update facets array
                            this.facets = response.facets;

                            instance.search.saveSearchInUrl(this.query, this.pagination.currentPage, this.pagination.resultsPerPage, this.filters, this.sorting, this.geoFilter);

                            Vue.nextTick(() => {

                                // 'open' category tree on DOM to show current selected category
                                this.refreshCategoryTree();
                            });
                    });
                },

                setSort(criteria, direction) {
                    if(criteria ===  null) {
                        this.sorting = null;
                    } else {
                        this.sorting = {};
                        this.sorting[criteria] = direction;
                    }
                    this.refresh();
                },
                
                setCurrentCategory() {
                    
                },

                // pagination
                goToPage(page) {
                    this.pagination.currentPage = page;

                    // got to page top
                    let $container = $('html, body');
                    let $ref = $(`#${templateContainerId}`);
                    $container.animate({ scrollTop: $ref.offset().top - $container.offset().top }, 'fast');

                    this.refresh();
                },

                toggleFilter(facetName, variantName) {
                    if(this.filters.hasOwnProperty(facetName) && this.filters[facetName] === variantName) {
                        // Clear filter
                        Vue.delete(this.filters, facetName);
                    } else {
                        // Set filter
                        Vue.set(this.filters, facetName, variantName);
                    }
                    this.refresh();
                },

                changeResultsNumber(resultsPerPage) {
                    this.pagination.resultsPerPage = resultsPerPage;
                    this.refresh();
                },

                updateNumericFilter(facetName, min, max) {
                    if( ! this.filters[facetName]) {
                        this.filters[facetName] = {};
                    }
                    this.filters[facetName]['min'] = min;
                    this.filters[facetName]['max'] = max;
                    this.refresh();
                },

                clearFilters() {
                    // Clear everything except the selected category
                    for (let property in this.filters) {
                        if(property !== 'categories' && this.filters.hasOwnProperty(property)) {
                            Vue.delete(this.filters, property);
                        }
                    }

                    this.refresh();
                },

                imageStyle(product) {
                    return {
                        backgroundImage: 'url(' + this.imageUrl(product) + ')',
                        backgroundRepeat: 'no-repeat',
                        backgroundPosition: 'center',
                        backgroundSize: 'cover',
                    }
                },

                imageUrl(product) {
                    if(product.mainImage != null) {
                        return "{{ apiUrl }}/image/" + product.mainImage.id + "?w=500&h=500";
                    } else {
                        return "{{ asset('images/legacy/no-image.jpg') }}"
                    }
                },

                getDiscount(product) {
                    if( ! product.crossedOutPrice ) { return }

                    let oldPrice = product.crossedOutPrice;
                    let newPrice = product.minimumPrice;
                    let discount = ((newPrice - oldPrice) * 100 / oldPrice);
                    return Math.round(discount);
                },

                isNewProduct(product) {
                    let oldDate = moment().subtract(newProductLifeSpan, 'days');
                    return moment.unix(product.createdAt).isAfter(oldDate);
                },

                refreshCategoryTree() {
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

                isCurrentCategory(categoryId) {
                    // return this.filters['categories'] == categoryId;
                },

                submitBasket(declinationId) {
                    $.ajax({
                        type: "POST",
                        url: Routing.generate("add_to_basket"),
                        data: { declinationId: declinationId, quantity: 1 },
                        success(response) {
                            hydrateModal(response.addedProduct, response.message);
                        }
                    });
                },

                range(a, b) {
                    let rangeArray = [];

                    for ( let i = a; i < b; i++ ) {
                        rangeArray.push(i);
                    }

                    return rangeArray;
                },
            },

            mounted() {

                // get current category (if set) from url to update filters parameter
                // let currentCategory = this.getCurrentCategory();
                //
                // if(currentCategory) {
                //     this.filters['categories'] = currentCategory;
                // }

                
                // trigger the first refresh
                this.refresh();
            },

            filters: {
                price: W.formatPrice
            }
        });
    }
}
