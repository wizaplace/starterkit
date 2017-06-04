/**
 * Search API.
 */

export class SearchClient {
    
    constructor(productSearchEndpoint, productAutocompleteEndpoint) {
        this.productSearchEndpoint = productSearchEndpoint;
        this.productAutocompleteEndpoint = productAutocompleteEndpoint;
    }

    /**
     * @param {string} query
     * @param {int} currentPage
     * @param {int} resultsPerPage
     * @param {Object} filters Key-value map of filters
     * @param {Object} sorting Key-value map of sorting
     * @param {Object} geoFilter Contains lat, lng, radius and label
     * @param {Object} extra Key-value map of extra parameters specific to the search engine implementation
     * @param {function} successCallback Signature: function(results)
     */
    searchProducts(
        query,
        currentPage,
        resultsPerPage,
        filters,
        sorting,
        geoFilter,
        extra,
        successCallback
    ) {

        let parameters = {
            query: query,
            page: currentPage,
            resultsPerPage: resultsPerPage,
            extra: extra
        };

        // add filters to parameters
        for( let name in filters ) {

            // filter out unwanted data
            if( ! filters.hasOwnProperty(name) ) { continue; }

            let key = 'filters[' + name + ']';
            parameters[key] = filters[name];
        }

        // add sorting to parameters
        if( sorting !== null ) {
            parameters.sorting = sorting;
        }

        // add geoFilter to parameters
        if( geoFilter !== null ) {
            parameters.geo = geoFilter;
        }

        // ajax call
        let request = $.get(this.productSearchEndpoint, parameters);

        request.done(function (data) {

            // return search result
            successCallback(data);
        });

        request.fail(function (jqXHR, textStatus) {
            console.error('Error while searching products: ' + textStatus);
        });
    }

    /**
     * @param {string} query
     * @param {function} successCallback Signature: function(results)
     */    
    autocomplete(query, successCallback) {
        let request = $.get(this.productAutocompleteEndpoint, {
            query: query
        });
        
        request.done(function (data) {
            successCallback(data);
        });
        
        request.fail(function (jqXHR, textStatus) {
            console.error('Error while autocompleting products: ' + textStatus);
        });
    }

    /**
     * Save search parameters in the URL (as query parameters).
     * Requires the URI.js library.
     */
    saveSearchInUrl(
        query,
        page,
        resultsPerPage,
        filters,
        sorting,
        geoFilter
    ) {
        let uri = new URI();

        // remove filters from current query (so we can set only the ones we were passed in params)
        let currentQuery = uri.search(true);
        
        for( let name in currentQuery ) {

            // filter out unwanted data
            if( ! currentQuery.hasOwnProperty(name) ) { continue; }

            if( isSpecialFilter(name) || isInt(name) || name.indexOf("sort") === 0 ) {
                uri.removeQuery(name);
            }
        }

        for( let name in filters ) {

            // filter out unwanted data
            if( ! filters.hasOwnProperty(name) ) { continue; }
            
            let value = filters[name];
            
            // numeric range filter (eg. price range)
            if( value.hasOwnProperty('min') || value.hasOwnProperty('max') ) {

                if( value.hasOwnProperty('min') ) {
                    uri.setQuery(name + '[min]', filters[name]['min']);
                }
                
                if( value.hasOwnProperty('max') ) {
                    uri.setQuery(name + '[max]', filters[name]['max']);
                }
                
            } else {
                uri.setQuery(name, filters[name]);
            }
        }
        
        if( query ) {
            uri.setQuery('q', query);
        } else {
            uri.removeQuery('q');
        }
        
        if( page !== 1 ) {
            uri.setQuery('page', page);
            uri.setQuery('perPage', resultsPerPage);

        } else {
            uri.removeQuery('page');
            uri.removeQuery('perPage');
        }
        
        for( let sortName in sorting ) {
            if( sorting.hasOwnProperty(sortName) ) {
                uri.setQuery('sort[' + sortName +']', sorting[sortName]);
            }
        }
        
        if( geoFilter ) {
            uri.setQuery('geo[lat]', geoFilter.lat);
            uri.setQuery('geo[lng]', geoFilter.lng);
            uri.setQuery('geo[radius]', geoFilter.radius);
            uri.setQuery('geo[label]', geoFilter.label);

        } else {
            uri.removeQuery('geo[lat]');
            uri.removeQuery('geo[lng]');
            uri.removeQuery('geo[radius]');
            uri.removeQuery('geo[label]');
        }
        
        history.replaceState({}, document.title, uri.toString());
    }

    /**
     * Restore search parameters from the URL (query parameters).
     * Requires the URI.js library.
     *
     * @return {{ query: String, page: Number, resultsPerPage: Number, sorting: Object, filters: Object }}
     */
    restoreSearchFromUrl() {

        let result = {
            query: null,
            currentPage: null,
            resultsPerPage: null,
            sorting: null,
            geoFilter: null,
            filters: null,
        };

        // get parameters from the URL
        let parameters = getUriParameters();

        // hydrate custom made 'result' object with extracted parameters
        for( let name in parameters ) {

            // filter out unwanted data
            if( ! parameters.hasOwnProperty(name) ) { continue; }

            let value = parameters[name];

            if( name === 'q' ) {
                result.query = value;

            } else if( name === 'page' ) {
                result.currentPage = value;

            } else if( name === 'perPage' ) {
                result.resultsPerPage = value;

            } else if( name === 'sort' ) {
                result.sorting = value;

            } else if( name === 'geo' ) {
                result.geoFilter = value;

            } else if( isSpecialFilter(name) || isInt(name) ) {

                if( result.filters === null ) {
                    result.filters = {};
                }

                result.filters[name] = value;
            }
        }

        return result;
    }
}

// 'private' functions (callable only inside this module)
// ======================================================

// decode URL parameters, including arrays (which URI.js doesn't do natively)
function getUriParameters() {

    // get current URL parameters with data map
    let currentUri = new URI();
    let uriParameters = currentUri.query(true);

    // will be filled with parameters extracted from URL
    let parameters = {};

    // insert URL parameters into 'parameters'
    for( let name in uriParameters ) {

        // filter out unwanted data
        if( ! uriParameters.hasOwnProperty(name) ) { continue; }

        // ignore empty parameters
        if( ! uriParameters[name] ) { continue; }

        // decode arrays (does not support nested arrays or indexed arrays)
        // fill 'parameters'
        let matches = name.match(/(.+)\[(.+)\]/);
        if( matches ) {

            if( ! parameters.hasOwnProperty(matches[1]) ) {
                parameters[matches[1]] = {};
            }

            parameters[matches[1]][matches[2]] = uriParameters[name];

        } else {
            parameters[name] = uriParameters[name];
        }
    }

    return parameters;
}

function isSpecialFilter(name) {
    return ["categories", "companies", "companyType", "price"].indexOf(name) !== -1
}

function isInt(val) {
    return ( ( typeof val === 'number' || typeof val === 'string' ) && val % 1 === 0 );
}
