/**
 * Search API.
 */

function SearchClient(productSearchEndpoint, productAutocompleteEndpoint) {

    /**
     * @param {string} query
     * @param {int} page
     * @param {int} resultsPerPage
     * @param {Object} filters Key-value map of filters
     * @param {Object} sorting Key-value map of sorting
     * @param {Object} geoFilter Contains lat, lng and radius
     * @param {Object} extra Key-value map of extra parameters specific to the search engine implementation
     * @param {function} success Signature: function(results)
     */
    this.searchProducts = function (query, page, resultsPerPage, filters, sorting, geoFilter, extra, success) {
        var parameters = {
            query: query,
            page: page,
            resultsPerPage: resultsPerPage,
            extra: extra
        };
        for (var filterName in filters) {
            if (filters.hasOwnProperty(filterName)) {
                var key = 'filters[' + filterName + ']';
                parameters[key] = filters[filterName];
            }
        }
        if (sorting !== null) {
            parameters.sorting = sorting;
        }
        if (geoFilter !== null) {
            parameters.geo = geoFilter;
        }

        var request = jQuery.get(productSearchEndpoint, parameters);
        request.done(function (data) {
            success(data);
        });
        request.fail(function (jqXHR, textStatus) {
            console.error('Error while searching products: ' + textStatus);
        });
    };

    /**
     * @param {string} query
     * @param {function} success Signature: function(results)
     */
    this.autocomplete = function (query, success) {
        var request = jQuery.get(productAutocompleteEndpoint, {
            query: query
        });
        request.done(function (data) {
            success(data);
        });
        request.fail(function (jqXHR, textStatus) {
            console.error('Error while autocompleting products: ' + textStatus);
        });
    };

    /**
     * Save search parameters in the URL (as query parameters).
     * Requires the URI.js library.
     */
    this.saveSearchInUrl = function (query, page, resultsPerPage, filters, sorting, geoFilter) {
        var uri = new URI();
        uri.query({}); // clear existing parameters
        for (var name in filters) {
            if (!filters.hasOwnProperty(name)) {
                continue;
            }
            value = filters[name];
            // Numeric filter
            if (value.hasOwnProperty('min') || value.hasOwnProperty('max')) {
                if (value.hasOwnProperty('min')) {
                    uri.addQuery(name + '[min]', filters[name]['min']);
                }
                if (value.hasOwnProperty('max')) {
                    uri.addQuery(name + '[max]', filters[name]['max']);
                }
            } else {
                uri.addQuery(name, filters[name]);
            }
        }
        if (query) {
            uri.addQuery('q', query);
        }
        if (page != 1) {
            uri.addQuery('page', page);
            uri.addQuery('perPage', resultsPerPage);
        }
        for (var sortName in sorting) {
            if (sorting.hasOwnProperty(sortName)) {
                uri.addQuery('sort[' + sortName +']', sorting[sortName]);
            }
        }
        if (geoFilter) {
            uri.addQuery('geo[lat]', geoFilter.lat);
            uri.addQuery('geo[lng]', geoFilter.lng);
            uri.addQuery('geo[radius]', geoFilter.radius);
        }
        history.replaceState({}, document.title, uri.toString());
    };

    /**
     * Restore search parameters from the URL (query parameters).
     *
     * Requires the URI.js library.
     *
     * @return {{ query: String, page: Number, resultsPerPage: Number, sorting: Object, filters: Object }}
     */
    this.restoreSearchFromUrl = function () {
        // Decode URI parameters, including arrays (which URI.js doesn't do)
        function getUriParameters() {
            var uriParameters = (new URI()).query(true);
            var parameters = {};
            for (var name in uriParameters) {
                if (!uriParameters.hasOwnProperty(name)) {
                    continue;
                }
                // Ignore empty parameters
                if (!uriParameters[name]) {
                    continue;
                }
                // Decode arrays (does not support nested arrays or indexed arrays yet)
                var matches = name.match(/(.+)\[(.+)\]/);
                if (matches) {
                    if (!parameters.hasOwnProperty(matches[1])) {
                        parameters[matches[1]] = {};
                    }
                    parameters[matches[1]][matches[2]] = uriParameters[name];
                } else {
                    parameters[name] = uriParameters[name];
                }
            }
            return parameters;
        }

        var result = {
            query: null,
            page: null,
            resultsPerPage: null,
            sorting: null,
            filters: null
        };

        // Read parameters from the URL
        var parameters = getUriParameters();
        for (var name in parameters) {
            if (!parameters.hasOwnProperty(name)) {
                continue;
            }
            var value = parameters[name];
            if (name === 'q') {
                result.query = value;
            } else if (name === 'page') {
                result.page = value;
            } else if (name === 'perPage') {
                result.resultsPerPage = value;
            } else if (name === 'sort') {
                result.sorting = value;
            } else if (name === 'geo') {
                result.geoFilter = value;
            } else {
                // Filters
                if (result.filters === null) {
                    result.filters = {};
                }
                result.filters[name] = value;
            }
        }

        return result;
    };

    return this;
}
