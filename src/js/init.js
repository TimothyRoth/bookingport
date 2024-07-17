/**
 * Storing Reserved Customer Items in a Set
 * The set makes it easier to handle the uniqueness of the IDs inside of it
 * Aswell as handling removing specific items. Using the letiable globally to
 * Be able to CRUD that exact set throughout several functions without passing parameters
 */
let preCartItems = new Set();

/**
 * Initialising the map globally to be able to change its options without rerendering the page
 */
let map;
let mapsApiLoaded = false;

jQuery(document).ready(function () {

    initMobileMenuNavigation();
    initBurgerMenu();
    initFAQ();
    initShowAdditionalCustomerContent();
    initShowAdditionalOrderContent();
    initControlCheckoutButton();
    initTriggerDenyOfferModal();
    initTriggerExpiredOfferModals();
    initDisplayStandTimer();
    initGenerateUserInvoice();
    initShowFeedbackForm();
    initPrintAllInvoices();
    initConditionalStandSelection();
    initPasswordStrength();
    initShowPasswordRequirements();

    // Stand Booking Form
    initStandBookingModal();
    initAddRequiredToRegistrationInputsWhenCheckboxIsChecked();
    initDisplayFurtherContentOnSelectChange();
    initAgreeOnTermsToSubmit();
    initShowCurrentStandSelection();

    /*** Ajax ***/
    initGoogleMapsStandsList();
    initGoogleMapsStandsListAdmin();
    initShowAccessibleStandsFilter();
    initStandFilterFunction();
    initAddItemsPreCart();
    initDeletePreCartItem();
    initDeleteOffer();
    initFilterExpiredOffers();
    initReactivateOffer();
    initResetReservedItemsOnCart();
    initEmptyCart();
    initAddItemsToCart();
    initUserWantsPavillon();
    initUpdateOrderRemarks();
    initCheckoutPageHTMLAdjustments();
    initFilterCustomers();
    initShowPreferedStreetResults();
    initShowAdminStandBookingCustomerResults();
    initAdminStandsFilter();
    initAdminOrderFilter();
    initSendCustomerRequest();
    initDenyCustomerRequest();
    initEditCustomerRequest();
    initProceedCustomerRequest();
    initAddOrderNote();
    initDenyOffer();
    initResetStand();
    initExportBookingStatus();

    // Woocommerce Translations
    initTranslateCreditCardCheckbox();
})

/** ajax and/or callback functions **/

function inputLimiter(func, wait, immediate) {
    let timeout;
    return function () {
        const context = this, args = arguments;
        const later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};

function loadMapsAPIKey() {

    if (!mapsApiLoaded) {
        const apiKey = googleMapsPluginSettings['google_api_key'];
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}`;
        document.head.appendChild(script);
        mapsApiLoaded = true;
    }

}

function renderCurrentStandSelection() {
    const currentItemsContainer = jQuery('.show-current-selection');
    const reservedItemsContainer = jQuery('.show-current-reservations');
    const currentItemsParent = jQuery('.current-selection-wrapper');
    const reservedItemsParent = jQuery('.currently-reserved-wrapper');
    const items = Array.from(preCartItems);
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'show_current_pre_cart_selection', items: items
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data) {

            if (data.current.length > 0 || data.reserved.length > 0) {
                jQuery('.customer-current-stand-status-info-container').addClass('active');
            } else {
                jQuery('.customer-current-stand-status-info-container').removeClass('active');
            }

            data.current.length > 0 ? currentItemsParent.addClass('active') : currentItemsParent.removeClass('active');
            data.reserved.length > 0 ? reservedItemsParent.addClass('active') : reservedItemsParent.removeClass('active');
            currentItemsContainer.html("");
            reservedItemsContainer.html("");

            jQuery.each(data.current, function (index, stand) {
                currentItemsContainer.append("<a href='#' class='single-stand'><img src='" + window.location.origin + "/wp-content/plugins/bookingport/assets/images/icons/stands/stand-marker-blue.svg' alt='Stand Icon'> <span class='stand-name-value'>" + stand + "</span></a>");
            });

            jQuery.each(data.reserved, function (index, stand) {
                reservedItemsContainer.append("<div class='single-stand'><img src='" + window.location.origin + "/wp-content/plugins/bookingport/assets/images/icons/stands/stand-marker-blue.svg' alt='Stand Icon'> <span class='stand-name-value'>" + stand + "</span></div>");
            });

            /* Putting the text value of the single stand into the search bar on click */
            const searchBar = jQuery('input[name="filter_prefered_street"]');
            jQuery('body').on('click', '.stand-name-value', function () {
                const input = jQuery(this).text();
                jQuery('.single-stand').removeClass('active');
                jQuery(this).parent('.single-stand').addClass('active');
                searchBar.val(input).trigger('keyup');
            });
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function areRequiredFieldsFilled() {
    let filled = true;
    let notFilledElement = null;
    const inputFields = jQuery('input[required]');
    inputFields.removeAttr('id');

    inputFields.each(function () {
        if (jQuery(this).val() === '') {
            filled = false;
            jQuery(this).attr('id', 'not-filled');
            notFilledElement = jQuery(this);
            return false;
        }
    });

    if (!filled && notFilledElement !== null) {
        alert('Bitte füllen Sie alle Pflichtfelder aus, um fortzufahren.');

        jQuery('html, body').animate({
            scrollTop: notFilledElement.offset().top - 100
        }, 500);
    }

    return filled;
}

function uncheckAllCheckboxes() {
    const checkboxes = jQuery('input[name="select_stand"]');
    checkboxes.prop('checked', false);
}

function exportBookingStatus() {
    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'getStandsStatusList_Export'
        }, beforeSend: function () {
            jQuery('.download-excel-modal').addClass('active');
        }, success: function (data, textStatus, XMLHttpRequest) {
            window.location.href = data;
        }, complete: function () {
            jQuery('.download-excel-modal').removeClass('active');
        }
    });
}

function initGoogleMapsStandsListAdmin(filter = null) {
    const mapsContainer = jQuery('#admin-stand-overview-map');

    if (mapsContainer.length > 0) {
        loadMapsAPIKey();
        jQuery.ajax({
            type: 'POST', url: ajax.url, dataType: 'json', data: {
                action: 'getGoogleMapsStandsList_Admin', filter: filter
            }, beforeSend: function () {
            }, success: async function (data, textStatus, XMLHttpRequest) {
                const satellite_view = googleMapsPluginSettings['google_satellite_view'];
                let map_type;
                satellite_view === "1" ? map_type = mapType = google.maps.MapTypeId.SATELLITE : mapType = google.maps.MapTypeId.ROADMAP;
                /* Initialize the map */
                map = new google.maps.Map(document.getElementById('admin-stand-overview-map'), {
                    center: {
                        lat: parseFloat(googleMapsPluginSettings['google_map_center_lat']),
                        lng: parseFloat(googleMapsPluginSettings['google_map_center_lng'])
                    },
                    zoom: parseInt(googleMapsPluginSettings['google_map_zoom_level_admin']),
                    mapTypeId: mapType,
                    draggable: true
                });


                let markers = [];
                let currentInfoWindow = null;

                /* Handle the markers for the freespace objects */

                let freespace;

                try {
                    freespace = await getGoogleMapsFreespace();
                    if (freespace.length > 0) {
                        jQuery.each(freespace, function (index, object) {
                            const markerIcon = {
                                url: googleMapsPluginSettings['google_freespace_icon'],
                                scaledSize: new google.maps.Size(20, 20)
                            }

                            let marker = new google.maps.Marker({
                                position: {
                                    lat: parseFloat(object.latitude), lng: parseFloat(object.longitude)
                                }, map: map, icon: markerIcon
                            });

                            const infoWindow = new google.maps.InfoWindow({
                                content: '<div class="object-info-text">' + object.infoText + '</div>'
                            });

                            marker.addListener('click', function () {
                                if (currentInfoWindow) {
                                    currentInfoWindow.close();
                                }
                                infoWindow.open(map, marker);
                                currentInfoWindow = infoWindow;
                            });

                            markers.push(marker);
                        })
                    }
                } catch (error) {
                    console.log(error);
                }

                /* Handle the markers for the Stands */
                let selectedMarker = null;

                jQuery.each(data, function (index, item) {
                    let markerIcon;

                    if (item.stand_status === "0") {
                        markerIcon = {
                            url: window.location.origin + '/wp-content/plugins/bookingport/assets/images/icons/stands/stand-marker-blue.svg',
                            scaledSize: new google.maps.Size(20, 28)
                        };
                    } else {
                        markerIcon = {
                            url: window.location.origin + '/wp-content/plugins/bookingport/assets/images/icons/stands/stand-marker-red.svg',
                            scaledSize: new google.maps.Size(20, 28)
                        };
                    }

                    let marker = new google.maps.Marker({
                        position: {
                            lat: parseFloat(item.lat), lng: parseFloat(item.lng)
                        }, map: map, icon: markerIcon, attributes: {
                            itemID: item.post_id
                        }, opacity: .8
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: '<div class="object-info-text"><p></strong> ' + item.title + '</p><a href="#filter-result-container">zur Bearbeitung</a></div>'
                    });
                    marker.addListener('click', function () {

                        if (selectedMarker !== marker) {
                            if (selectedMarker) {
                                selectedMarker.setOpacity(.8);
                                selectedMarker.setIcon(selectedMarker.getIcon());
                            }
                        }

                        marker.setOpacity(1);
                        marker.setIcon(marker.getIcon());

                        selectedMarker = marker;

                        if (currentInfoWindow) {
                            currentInfoWindow.close();
                        }
                        infoWindow.open(map, marker);
                        currentInfoWindow = infoWindow;


                        let query = {
                            dropdown: 'all', search: '', amount: 5
                        };

                        filterAdminStands(query, this.attributes.itemID);
                        filterStands('', this.attributes.itemID);
                    })

                    markers.push(marker); // Add the marker to the array
                });
            }, complete: function () {
                // Hide loader or loading state if needed
            }
        });
    }
}

function initGoogleMapsStandsList(filter = null) {
    const mapsContainer = jQuery('#customer-stand-booking-map');

    if (mapsContainer.length > 0) {
        loadMapsAPIKey();
        jQuery.ajax({
            type: 'POST', url: ajax.url, dataType: 'json', data: {
                action: 'getGoogleMapsStandsList', filter: filter
            }, beforeSend: function () {
                // Show loader or loading state if needed
            }, success: async function (data, textStatus, XMLHttpRequest) {
                const satellite_view = googleMapsPluginSettings['google_satellite_view'];
                let map_type;
                satellite_view === "1" ? map_type = mapType = google.maps.MapTypeId.SATELLITE : mapType = google.maps.MapTypeId.ROADMAP;
                /* Initialize the map */
                map = new google.maps.Map(document.getElementById('customer-stand-booking-map'), {
                    center: {
                        lat: parseFloat(googleMapsPluginSettings['google_map_center_lat']),
                        lng: parseFloat(googleMapsPluginSettings['google_map_center_lng'])
                    },
                    zoom: parseInt(googleMapsPluginSettings['google_map_zoom_level_stand_booking']),
                    mapTypeId: map_type,
                    draggable: true
                });

                let markers = [];
                let currentInfoWindow = null;

                /* Handle the markers for the freespace objects */

                let freespace;

                try {
                    freespace = await getGoogleMapsFreespace();
                    if (freespace.length > 0) {
                        jQuery.each(freespace, function (index, object) {

                            const markerIcon = {
                                url: googleMapsPluginSettings['google_freespace_icon'],
                                scaledSize: new google.maps.Size(20, 20)
                            }

                            let marker = new google.maps.Marker({
                                position: {
                                    lat: parseFloat(object.latitude), lng: parseFloat(object.longitude)
                                }, map: map, icon: markerIcon
                            });

                            const infoWindow = new google.maps.InfoWindow({
                                content: '<div class="object-info-text">' + object.infoText + '</div>'
                            });

                            marker.addListener('click', function () {
                                if (currentInfoWindow) {
                                    currentInfoWindow.close();
                                }
                                infoWindow.open(map, marker);
                                currentInfoWindow = infoWindow;
                            });

                            markers.push(marker);
                        })
                    }
                } catch (error) {
                    console.log(error);
                }

                /* Handle the markers for the Stands */
                let selectedMarker = null;

                jQuery.each(data, function (index, item) {

                    let markerIcon;

                    if (item.stand_status === "0") {
                        markerIcon = {
                            url: window.location.origin + '/wp-content/plugins/bookingport/assets/images/icons/stands/stand-marker-blue.svg',
                            scaledSize: new google.maps.Size(20, 28)
                        };
                    } else {
                        markerIcon = {
                            url: window.location.origin + '/wp-content/plugins/bookingport/assets/images/icons/stands/stand-marker-grey.svg',
                            scaledSize: new google.maps.Size(20, 28)
                        };
                    }

                    let selectedMarkerIcon = {
                        url: window.location.origin + '/wp-content/plugins/bookingport/assets/images/icons/stands/stand-marker-green.svg',
                        scaledSize: new google.maps.Size(24, 32)
                    };

                    let marker = new google.maps.Marker({
                        position: {
                            lat: parseFloat(item.lat), lng: parseFloat(item.lng)
                        }, map: map, icon: markerIcon, attributes: {
                            itemID: item.post_id
                        }
                    });

                    if (item.stand_status !== "0") {
                        const infoWindow = new google.maps.InfoWindow({
                            content: '<div class="object-info-text">Der Stand <strong>' + item.title + '</strong> ist zur Zeit leider nicht verfügbar.</div>'
                        });
                        marker.addListener('click', function () {
                            if (currentInfoWindow) {
                                currentInfoWindow.close();
                            }
                            infoWindow.open(map, marker);
                            currentInfoWindow = infoWindow;
                        })
                    } else {
                        const infoWindow = new google.maps.InfoWindow({
                            content: '<div class="object-info-text"><p></strong> ' + item.title + '</p><a href="#stand-selection">zur Standauswahl</a></div>'
                        });
                        marker.addListener('click', function () {
                            if (currentInfoWindow) {
                                currentInfoWindow.close();
                            }
                            infoWindow.open(map, marker);
                            currentInfoWindow = infoWindow;
                        })
                    }

                    if (item.stand_status === "0") {

                        marker.addListener('click', function () {

                            if (selectedMarker) {
                                selectedMarker.setIcon(markerIcon); // Restore the icon of the previously selected marker
                            }

                            this.setIcon(selectedMarkerIcon); // Set the icon of the clicked marker to the selected icon
                            selectedMarker = this; // Update the selectedMarker letiable

                            let query = {
                                dropdown: 'all', search: '', amount: 5
                            };

                            filterAdminStands(query, this.attributes.itemID);
                            filterStands('', this.attributes.itemID);
                        });
                    }

                    markers.push(marker); // Add the marker to the array
                });
            }, complete: function () {
                // Hide loader or loading state if needed
            }
        });
    }
}

async function getGoogleMapsFreespace() {

    try {
        const data = await jQuery.ajax({
            type: 'POST', url: ajax.url, dataType: 'json', data: {
                action: 'get_map_freespace'
            }
        });

        return data;
    } catch (error) {
        console.error(error);
        throw error;
    }
}

function addOrderNote(orderMeta) {
    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'add_order_note', orderMeta: orderMeta
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {
            // do something
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function filterInvoices(query) {
    const adminPanelOrderOverviewContainer = jQuery('.admin-order-results');

    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'filter_invoices', orderMeta: query,
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {
            adminPanelOrderOverviewContainer.html(data);
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function filterCustomers(query) {

    const adminPanelCustomerOverviewContainer = jQuery('.customer-search-results');
    const adminPanelCustomerStandBookingContainer = jQuery('.select-customer-container .customer-results ul');

    let StandBookingContainerHtml = [];

    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'filter_customers', dropdown: query.dropdown, search: query.search, amount: query.amount
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {

            if (adminPanelCustomerStandBookingContainer.length) {

                jQuery.each(data.user_stand_booking_meta, function (index, single_customer) {
                    const content = `
                        <li class="single-customer" data-src="${single_customer.user_id}">${single_customer.user_name} (${single_customer.user_email})</li>
                        `
                    StandBookingContainerHtml.push(content);
                });

                adminPanelCustomerStandBookingContainer.html(StandBookingContainerHtml);
            }

            if (adminPanelCustomerOverviewContainer.length) {
                adminPanelCustomerOverviewContainer.html(data.html);
            }
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function filterAdminStands(query, itemID = null) {

    const container = jQuery('#filter-result-container');

    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'admin_map_stand_filter',
            dropdown: query.dropdown,
            search: query.search,
            amount: query.amount,
            itemID: itemID
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {
            container.html(data.html);
            // Returns true if its an Array, otherwise false. In this case when its not an empty array, its an object. Thats when we want to execute our code
            if ((!Array.isArray(data.geo) && data.geo !== null)) {
                map.setCenter({lat: parseFloat(data.geo.lat), lng: parseFloat(data.geo.lng)});
            }
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function addOrderRemarks(remarks) {

    const commercialCustomerContainer = jQuery('.prefered-street-results');

    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'add_order_remarks', remarks: remarks
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {
            if (!commercialCustomerContainer.length) window.location.href = '/warenkorb';
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function sendCustomerRequest(standMeta) {

    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'send_customer_request', stand: standMeta
        }, beforeSend: function () {
        }, success: function (data, textStatus, XMLHttpRequest) {
            window.location.href = data;
        }, complete: function () {
        }
    });
}

function updateOrderRemarks(remarks) {

    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'update_order_remarks', remarks: remarks
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function updateItemsPreCartDomElements() {

    const container = jQuery('#filter-prefered-street-result-container .inner-content');
    const checkboxes = jQuery('input[name="select_stand"]');

    container.on('click', function (event) {
        const checkbox = jQuery(this).find('input[name="select_stand"]');
        const container = jQuery(this);
        const isChecked = checkbox.prop('checked');
        const checkboxID = checkbox.attr('id');

        if (isChecked) {
            checkbox.prop('checked', false);
            // Remove the unchecked checkbox ID from the items Set
            preCartItems.delete(checkboxID);
            container.removeClass('active');
        } else {
            checkbox.prop('checked', true);
            // Add the checked checkbox ID to the items Set
            preCartItems.add(checkboxID);
            container.addClass('active');
        }
    });

    // Checkbox click event handler
    checkboxes.each(function () {
        const checkboxID = jQuery(this).attr('id');
        const isChecked = preCartItems.has(checkboxID);

        if (isChecked) {
            jQuery(this).prop('checked', true);
            jQuery(this).parent().parent().addClass('active');
        }
    });

    checkboxes.on('click', function (event) {
        event.stopPropagation();
        const isChecked = jQuery(this).prop('checked');
        const checkboxID = jQuery(this).attr('id');

        if (isChecked) {
            // Add the checked checkbox ID to the items Set
            preCartItems.add(checkboxID);
        } else {
            // Remove the unchecked checkbox ID from the items Set
            preCartItems.delete(checkboxID);
        }
    });
}

function addItemsToCart() {
    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'add_items_to_cart'
        }, beforeSend: function () {
        }, success: function (data, textStatus, XMLHttpRequest) {
            const remarks = jQuery('textarea[name="user_remarks"]').val();
            addOrderRemarks(remarks);
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function printAllInvoices() {
    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'print_all_invoices',
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {

            jQuery.each(data, function (index, orderID) {
                printUserInvoice(orderID)
            })
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function updateItemPavillonStatus(item) {
    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'item_pavillon_status', isChecked: item.isChecked, itemID: item.id
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {
            // do something
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function filterStands(query, itemID = null) {

    const privateCustomerContainer = jQuery('#filter-prefered-street-result-container');
    const commercialCustomerContainer = jQuery('.prefered-street-results ul');

    let html = [];
    let content = null;

    jQuery.ajax({
        type: 'POST', url: ajax.url, dataType: 'json', data: {
            action: 'filter_stands', searchQuery: query, itemID: itemID
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data, textStatus, XMLHttpRequest) {
            // Update the container with the filtered results
            // do something
            let counter = 0;
            if (!commercialCustomerContainer.length) {
                jQuery.each(data, function (index, singleStand) {
                    if (!singleStand.message) {

                        // Respond to the Input with the map
                        if (counter >= 0) {
                            map.setCenter({lat: parseFloat(singleStand.geo.lat), lng: parseFloat(singleStand.geo.lng)});
                        }
                        counter++;

                        content = `
                    <div class="single-result">
                        <div class="inner-content">
                            <div class="row selected-stand-street">
                                <img class="stand-marker-image"
                                     src="${singleStand.image_urls.marker}">
                                <p class="stand-street">${singleStand.street}</p>
                            </div>
                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="${singleStand.image_urls.number}">
                                <p class="stand-number stand-street">Standnummer: ${singleStand.number} </p>
                            </div>
                            ${singleStand.size ? `
                                  <div class="row selected-stand-space">
                                    <img class="stand-space-image" src="${singleStand.image_urls.space}">
                                    <p class="stand-space">${singleStand.size}</p>
                                  </div>
                                ` : ''}
                             ${singleStand.price ? `
                              <div class="row selected-stand-number">
                                <img class="stand-number-image" src="${singleStand.image_urls.number}">
                                <p class="stand-number stand-street">${singleStand.price} €</p>
                              </div>
                            ` : ''}
                            <div class="select-stand-wrapper">
                                <input type="checkbox" name="select_stand" id="${singleStand.id}">
                            </div>
                            ${singleStand.has_pavillon ? `
                                <div class="row selected-stand-pavillon">
                                    <img class="stand-pavillon-image"
                                         src="${singleStand.image_urls.pavillon}">
                                    <p class="stand-pavillon">Pavillon möglich</p>
                                </div>
                            ` : `
                             <div class="row selected-stand-pavillon">
                                    <img class="stand-pavillon-image"
                                         src="${singleStand.image_urls.pavillon}">
                                    <p class="stand-pavillon">Kein Pavillon möglich</p>
                                </div>
                            `}
                        </div>
                    </div>
                `;
                    } else {
                        content = `
                        <p>${singleStand.message}</p>
                    `
                    }
                    html.push(content);

                });
                privateCustomerContainer.html(html);
            }
            if (commercialCustomerContainer.length) {
                jQuery.each(data, function (index, singleStand) {
                    if (!singleStand.message) {
                        content = `
                            <li class="stand-item" data-attribute="${singleStand.id}">${singleStand.street} (${singleStand.number})</li>
                            `;
                    } else {
                        content = `
                        <li>${singleStand.message}</li>
                    `
                    }
                    html.push(content);

                });
                commercialCustomerContainer.html(html);
            }
            updateItemsPreCartDomElements();
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}


function denyAdminOffer(request, message) {

    const container = jQuery('.request-container');
    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'deny_admin_offer', requestToDelete: request, customerMessage: message
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data) {
            // do something
        },

        complete: function () {
            jQuery(window).scrollTop(0);
            window.location.reload();
        }
    });
}

function resetStand(itemToDelete) {

    const container = jQuery('.request-container');
    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'reset_stand_meta', itemToDelete: itemToDelete
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data) {
            window.location.reload();
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function denyCustomerRequest(requestData) {

    const container = jQuery('.request-container');
    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'deny_customer_request',
            itemToDelete: requestData.itemToDelete,
            requestToDelete: requestData.requestToDelete
        }, beforeSend: function () {
            console.log(requestData);
            // Show loader or loading state if needed
        }, success: function (data) {
            container.html(data);
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function proceedCustomerRequest(proceedMeta) {

    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'proceed_customer_request', proceedMeta: proceedMeta
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data) {
            jQuery(window).scrollTop(0);
            window.location.href = data;
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function printUserInvoice(orderID) {
    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'print_user_invoice', orderID: orderID
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (response) {
            if (response && response.pdf_data) {
                // Convert base64 PDF data to Blob
                let contentType = 'application/pdf';
                let byteCharacters = atob(response.pdf_data);
                let byteArrays = [];
                for (let offset = 0; offset < byteCharacters.length; offset += 512) {
                    let slice = byteCharacters.slice(offset, offset + 512);
                    let byteNumbers = new Array(slice.length);
                    for (let i = 0; i < slice.length; i++) {
                        byteNumbers[i] = slice.charCodeAt(i);
                    }
                    let byteArray = new Uint8Array(byteNumbers);
                    byteArrays.push(byteArray);
                }
                let pdfBlob = new Blob(byteArrays, {type: contentType});

                // Create a download link for the PDF
                let downloadLink = document.createElement('a');
                downloadLink.href = URL.createObjectURL(pdfBlob);
                downloadLink.download = 'rechnung#' + orderID + '.pdf';
                downloadLink.click();
            } else {
                console.log('PDF generation failed.');
            }
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function deletePreCartItem(itemToDelete) {
    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'delete_item', itemToDelete: itemToDelete
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data) {
            preCartItems.clear();
            addItemsPreCart(preCartItems);
            uncheckAllCheckboxes();
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function emptyCart() {
    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'empty_cart',
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data) {
            // do something
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function addItemsPreCart(items, editRequest = false) {

    const container = jQuery('.selected-stands #selected-stands-container');
    const errorContainer = jQuery('#error-messages');

    /* Emptying the Container on each function call to prevent double error messages */

    errorContainer.empty();
    let errorMessages = [];
    const html = [];

    // Perform AJAX call
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'update_sell_status', items: items, editRequest: editRequest
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (data) {

            const currentCartLength = Object.keys(data.current_cart_selection).length;
            if (currentCartLength > 0) {
                jQuery('.selected-stands').removeClass('hide');
                jQuery('.no-selected-stands').addClass('hide');
            } else {
                jQuery('.selected-stands').addClass('hide');
                jQuery('.no-selected-stands').removeClass('hide');
            }
            preCartItems.clear();

            errorContainer.html('');
            if (Object.keys(data.error_messages).length > 0) {

                jQuery.each(data.error_messages, function (index, singleMessage) {
                    errorMessages.push(singleMessage.message);
                });

                errorContainer.html('<p class="error-messages">' + errorMessages + '</p>');

            }

            let counter = 1;
            jQuery.each(data.response, function (index, singleStand) {
                content = `
                    <div class="single-result">
                        <div class="upper-row">
                            <p>Stand ${counter}</p>
                            <div class="delete-item" data-src="${singleStand.id}">Stand löschen <img src="${singleStand.image_urls.delete}"></div>
                        </div>
                        <div class="inner-content">
                            <div class="row selected-stand-street">
                                <img class="stand-marker-image"
                                     src="${singleStand.image_urls.marker}">
                                <p class="stand-street">${singleStand.street}</p>
                            </div>
                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="${singleStand.image_urls.number}">
                                <p class="stand-number stand-street">Standnummer: ${singleStand.number} </p>
                            </div>
                             ${singleStand.size ? `
                                  <div class="row selected-stand-space">
                                    <img class="stand-space-image" src="${singleStand.image_urls.space}">
                                    <p class="stand-space">${singleStand.size}</p>
                                  </div>
                                ` : ''}
                             ${singleStand.price ? `
                              <div class="row selected-stand-number">
                                <img class="stand-number-image" src="${singleStand.image_urls.number}">
                                <p class="stand-number stand-street">${singleStand.price} €</p>
                              </div>
                            ` : ''}
                        </div>
                        ${singleStand.has_pavillon ? `
                            <div class="checkbox-container">
                                <input id="user_pavillon_${counter}" type="checkbox" name="user_pavillon" data-src="${singleStand.id}">
                                <label for="user_pavillon_${counter}">Ich möchte am Stand einen Pavillon aufbauen</label>
                            </div>
                            ` : ''}
                    </div>
                `;
                counter++;

                html.push(content);

            });
            container.html(html);
        }, complete: function () {
            window.location.href = '#selected-stands-container';
        }
    });
}


/************************************************************************************************/


/** init functions **/
function initAgreeOnTermsToSubmit() {
    const checkbox = jQuery('#agree_on_terms input[type="checkbox"]');
    const button = jQuery('.form input[type="submit"]');

    checkbox.on('change', function () {
        button.toggleClass('disabled');
    })
}

function initDisplayFurtherContentOnSelectChange() {

    const selectField = jQuery('select[name="gastronomy"]');
    const container = jQuery('.further-steps');
    const standDepthContainer = jQuery('.hide-for-domestic');
    const standPriceContainer = jQuery('.show-for-domestic');
    const standPriceInput = jQuery('input[name="stand_price"]');
    const standWidthInput = jQuery('input[name="stand_width"]');

    selectField.on('change', function () {
        const value = selectField.val();
        container.removeClass('active');
        if (value !== "") container.addClass('active');

        /* Here we are handling the display status and required status of a field thats depending on the user selection */
        if (value === "anlieger") {
            standDepthContainer.addClass('hide');
            standPriceContainer.removeClass('hide');
            jQuery('input[name="stand_depth"]').removeAttr('required');
        } else {
            standDepthContainer.removeClass('hide');
            standPriceContainer.addClass('hide');
            jQuery('input[name="stand_depth"]').attr('required', 'required');
        }

        standWidthInput.on('keyup', inputLimiter(function (e) {
            e.preventDefault();
            if (isNaN(standWidthInput.val())) {
                standPriceInput.val("Keine Gültige Eingabe");
            } else {
                standPriceInput.val(standWidthInput.val() * parseFloat(standPriceInput.attr('data-attribute')));
            }
        }, 300));


    })
}

function initStandBookingModal() {
    const modal = jQuery('#privat-stand-booking-modal');
    const button = jQuery('.trigger-privat-stand-booking-modal');
    const close = jQuery('.close-privat-stand-booking-modal');
    const just_close_the_modal = jQuery('.close-stand-booking-modal');

    just_close_the_modal.on('click', function () {
        jQuery('.modal').removeClass('active');
        jQuery('body').removeClass('fixed');
    })

    button.click(function () {
        filterStands('');
        modal.addClass('active');
        renderCurrentStandSelection();
        setTimeout(function () {
            jQuery('body').addClass('fixed');
        }, 300);
    })

    close.on('click', function () {
        jQuery('body').removeClass('fixed');
        modal.removeClass('active');

        if (preCartItems.size > 0) {
            const anchorLink = jQuery('#headline-stand-selection');
            if (anchorLink.length > 0) {
                jQuery('html, body').animate({
                    scrollTop: anchorLink.offset().top - 100
                }, 500);
            }
        }
    })
}

function initAddRequiredToRegistrationInputsWhenCheckboxIsChecked() {
    const checkbox = jQuery('#form-alternative-billing-address input[type="checkbox"]');
    const inputFields = jQuery('#form-alternative-billing-address .itsRequired');
    const container = jQuery('.alternative-billing-address-content ');
    checkbox.on('change', function () {
        let isChecked = $(this).is(':checked');
        inputFields.prop('required', isChecked);
        isChecked ? container.addClass('active') : container.removeClass('active');
    })
}


function initStandFilterFunction() {

    const $searchInput = jQuery('#search-filter-wrapper input[name="filter_prefered_street"]');

    $searchInput.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        const searchQuery = jQuery(this).val();
        filterStands(searchQuery);
    }, 300));
}

function initAddItemsPreCart() {

    const addItems = jQuery('.close-privat-stand-booking-modal');
    const hideItems = jQuery('.no-selected-stands');
    const showItems = jQuery('.selected-stands');

    updateItemsPreCartDomElements();

    addItems.on('click', function () {
        uncheckAllCheckboxes();
        addItemsPreCart(Array.from(preCartItems));

        if (Array.from(preCartItems).length > 0) {
            hideItems.addClass('hide');
            showItems.removeClass('hide');
        }

    })
}

function initDeletePreCartItem() {

    jQuery('body').on('click', '.delete-item', function () {
        const itemToDelete = jQuery(this).attr('data-src');
        deletePreCartItem(itemToDelete);
        emptyCart();
        setTimeout(function () {
            filterStands('');
        }, 100);

    });
}

function initAddItemsToCart() {

    const button = jQuery('#user-checkout.privat');

    button.on('click', function () {
        addItemsToCart();
    })

}

function initUserWantsPavillon() {

    jQuery('body').on('click', 'input[name="user_pavillon"]', function () {

        const item = {
            isChecked: jQuery(this).prop('checked'), id: jQuery(this).attr('data-src')
        };

        updateItemPavillonStatus(item);
    })
}

function initEmptyCart() {

    jQuery('body').on('click', '.product-remove.remove_single_item a', function () {
        const itemToDelete = jQuery(this).parent().attr('data-src');
        deletePreCartItem(itemToDelete);
    });

}

function initCheckoutPageHTMLAdjustments() {
    const additionalFieldsHeading = jQuery('.woocommerce-additional-fields h3');
    const orderReviewHeading = jQuery('#order_review_heading');
    additionalFieldsHeading.html('Sonstiges');
    orderReviewHeading.html('Ihre Buchung');
}

function initUpdateOrderRemarks() {
    const textarea = jQuery('textarea[name="user_remarks"]');
    textarea.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        const remarks = jQuery(this).val();
        updateOrderRemarks(remarks);
    }, 300));
}

function initFilterCustomers() {

    let userAdminPanelQuery = {
        dropdown: '', search: '', amount: 5
    };

    const dropdown = jQuery('select[name="customer-dropdown-filter"]');
    const search = jQuery('input[name="customer-search"]');

    dropdown.on('change', function () {
        userAdminPanelQuery.dropdown = jQuery(this).val();
        userAdminPanelQuery.amount = 5;
        filterCustomers(userAdminPanelQuery);
    })

    search.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        userAdminPanelQuery.search = jQuery(this).val();
        userAdminPanelQuery.amount = 5;
        filterCustomers(userAdminPanelQuery);
    }, 300));

    jQuery('body').on('click', '.show-more-users', function () {
        userAdminPanelQuery.amount += 5;
        filterCustomers(userAdminPanelQuery);
    })

}


function initAdminOrderFilter() {

    let orderAdminPanelQuery = {
        dropdown: '', search: '', amount: 5
    }

    const dropdown = jQuery('select[name="order-dropdown-filter"]');
    const search = jQuery('input[name="order-search"]');

    if (search.length > 0) {
        setTimeout(function () {
            search.trigger('keyup')
        })
    }

    dropdown.on('change', function () {
        orderAdminPanelQuery.dropdown = jQuery(this).val();
        orderAdminPanelQuery.amount = 5;
        filterInvoices(orderAdminPanelQuery);
    })

    search.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        orderAdminPanelQuery.search = jQuery(this).val();
        orderAdminPanelQuery.amount = 5;
        filterInvoices(orderAdminPanelQuery);
    }, 300));

    jQuery('body').on('click', '.show-more-orders', function () {
        orderAdminPanelQuery.amount += 5;
        filterInvoices(orderAdminPanelQuery);
    })

}

function initAdminStandsFilter() {

    let standsAdminPanelQuery = {
        dropdown: 'all', search: '', amount: 5
    }

    const dropdown = jQuery('select[name="filter-stand-status"]');
    /* Every time the page refreshes, we reset the dropdown value */
    dropdown.val('all');

    const search = jQuery('input[name="admin-filter-stands"]');

    dropdown.on('change', function () {
        standsAdminPanelQuery.dropdown = jQuery(this).val();
        standsAdminPanelQuery.amount = 5;
        filterAdminStands(standsAdminPanelQuery);
        initGoogleMapsStandsListAdmin(standsAdminPanelQuery.dropdown);
    })

    search.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        standsAdminPanelQuery.search = jQuery(this).val();
        standsAdminPanelQuery.amount = 5;
        filterAdminStands(standsAdminPanelQuery);
    }, 300));

    jQuery('body').on('click', '.show-more-stands', function () {
        standsAdminPanelQuery.amount += 5;
        filterAdminStands(standsAdminPanelQuery);
    })

}

function initShowAdditionalCustomerContent() {

    jQuery('body').on('click', '.open-additional-customer-information', function () {
        jQuery(this).toggleClass('active');
        const container = jQuery(this).siblings('.hidden-user-information');
        container.toggleClass('active');
    });
}

function initShowAdditionalOrderContent() {

    jQuery('body').on('click', '.open-additional-order-information', function () {
        jQuery(this).toggleClass('active');
        const container = jQuery(this).siblings('.hidden-order-information');
        container.toggleClass('active');
    });
}

function initShowPreferedStreetResults() {
    const input = jQuery('input[name="prefered_address"]');
    const container = jQuery('.prefered-street-results ul');

    input.on('click', function () {
        filterStands('');
        container.addClass('active');
    })

    input.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        filterStands(jQuery(this).val());
    }, 300));

    jQuery('body').on('click', function (e) {
        if (!jQuery(e.target).is(container) && !jQuery(e.target).closest(container).length && !jQuery(e.target).is(input)) container.removeClass('active');
    })

    jQuery('body').on('click', '.stand-item', function () {
        const itemMeta = jQuery(this).html();
        input.val(itemMeta);
        input.attr('data-attribute', jQuery(this).attr('data-attribute'));
        container.removeClass('active');
    })
}

function initShowAdminStandBookingCustomerResults() {
    const input = jQuery('input[name="select_customer"]');
    const container = jQuery('.customer-results ul');

    let userStandBookingQuery = {
        dropdown: '', search: '', amount: Infinity
    }

    input.on('click', function () {
        filterCustomers(userStandBookingQuery);
        container.addClass('active');
    })

    input.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        userStandBookingQuery.search = jQuery(this).val();
        filterCustomers(userStandBookingQuery);
    }, 300));

    jQuery('body').on('click', function (e) {
        if (!jQuery(e.target).is(container) && !jQuery(e.target).closest(container).length && !jQuery(e.target).is(input)) container.removeClass('active');
    })

    jQuery('body').on('click', '.single-customer', function () {
        const itemMeta = jQuery(this).html();
        input.val(itemMeta);
        input.attr('data-attribute', jQuery(this).attr('data-src'));
        container.removeClass('active');
    })
}


function initSendCustomerRequest() {
    const buttons = jQuery('#user-checkout.gewerblich, #user-checkout.verein');

    let standMeta = {};

    jQuery('select[name="gastronomy"]').on('change', function () {
        jQuery('input[name="stand_width"]').val('');
        jQuery('input[name="stand_depth"]').val('');
        standMeta = {};
    })

    const preferedAddressInput = jQuery('input[name="prefered_address"]');

    buttons.on('click', function () {
        standMeta.id = jQuery('input[name="prefered_address"]').attr('data-attribute');
        standMeta.association_name = jQuery('input[name="association_name"]').val();
        standMeta.association_sortiment = jQuery('input[name="association_sortiment"]').val();
        standMeta.pavillon = jQuery('input[name="user_pavillon"]').prop('checked');
        standMeta.width = jQuery('input[name="stand_width"]').val();
        standMeta.depth = jQuery('input[name="stand_depth"]').val();
        standMeta.remarks = jQuery('textarea[name="user_remarks"]').val();

        // check if the input has a value and return error if it doesn't
        if (standMeta.id === undefined) {
            alert('Bitte wählen Sie Ihren Wunschstandort im Dropdown aus.');
            preferedAddressInput.attr('id', 'not-filled');
            jQuery('html, body').animate({
                scrollTop: preferedAddressInput.offset().top - 100
            }, 500)
            return 1;
        }

        if (areRequiredFieldsFilled()) sendCustomerRequest(standMeta);
        return 0;
    })
}

function initDenyCustomerRequest() {
    jQuery('body').on('click', '.deny-request', function () {
        const requestData = {};
        requestData.itemToDelete = jQuery(this).attr('data-stand-id');
        requestData.requestToDelete = jQuery(this).attr('data-request-id');
        denyCustomerRequest(requestData);

        // reverse all the changes we made in the edit-customer-request function
        const selectCustomerInput = jQuery('input[name="select_customer"]');
        const selectCustomerContainer = jQuery('.select-customer-container');
        selectCustomerContainer.removeClass('is-edit');
        selectCustomerInput.attr('data-attribute', '');
        selectCustomerInput.attr('required', 'true');
        jQuery('.select-request-container-content').html('');
        jQuery('input[name="request_id"]').val('');
    });
}

function initProceedCustomerRequest() {

    const button = jQuery('#user-checkout.administrator');
    let proceedMeta = {};

    const customerInput = jQuery('input[name="select_customer"]');

    button.on('click', function () {
        proceedMeta.customer_id = jQuery('input[name="select_customer"]').attr('data-attribute');
        proceedMeta.customer_name = jQuery('input[name="select_customer"]').val();
        proceedMeta.pavillon_confirmation = jQuery('input[name="user_pavillon_confirmation"]').prop('checked');
        proceedMeta.width = jQuery('input[name="select_width"]').val();
        proceedMeta.depth = jQuery('input[name="select_depth"]').val();
        proceedMeta.price = jQuery('input[name="select_price"]').val();
        proceedMeta.id = jQuery('input[name="request_id"]').val();

        // check if the input has a value and return error if it doesn't
        if (proceedMeta.customer_id === undefined) {
            alert('Bitte wählen Sie den Kunden im Dropdown aus.');
            customerInput.attr('id', 'not-filled');
            jQuery('html, body').animate({
                scrollTop: customerInput.offset().top - 100
            }, 500)
            return 1;
        }

        if (areRequiredFieldsFilled()) proceedCustomerRequest(proceedMeta);
        return 0;
    });
}

function initResetReservedItemsOnCart() {

    const button = jQuery('.product-remove.remove_array a');
    button.on('click', function () {
        const data = jQuery(this).parent().attr('data-src').split(', ');
        data.forEach(function (value) {
            deletePreCartItem(value)
        });
    })

}

function initTranslateCreditCardCheckbox() {

    setTimeout(function () {
        let validate = true;
        jQuery('body').on('change', function () {
            const checkboxText = jQuery('label[for="wc-woocommerce_payments-new-payment-method"]');
            if (checkboxText.length > 0 && validate) {
                checkboxText.html('Zahlungsinformationen für zukünftige Einkäufe speichern');
                validate = false;
            }
        })
    }, 500);
}

function initAddOrderNote() {
    jQuery('body').on('click', '.admin_add_order_note_button', function () {
        const textarea = jQuery(this).siblings('textarea[name="add_order_note"]');
        const noteText = textarea.val();
        const orderID = textarea.attr('data-src');
        const orderMeta = {};
        orderMeta.orderID = orderID;
        orderMeta.noteText = noteText;

        let currentDate = new Date();
        let day = currentDate.getDate();
        let month = currentDate.getMonth() + 1; // Months are zero-based, so we add 1
        let year = currentDate.getFullYear();
        let hours = currentDate.getHours();
        let minutes = currentDate.getMinutes()
        let formattedDateTime = day + "." + month + "." + year + " um " + hours + ":" + (minutes < 10 ? "0" + minutes : minutes) + " Uhr";

        if (noteText !== "") {
            addOrderNote(orderMeta);
            textarea.val("");
            textarea.attr('placeholder', 'Ihre Anmerkung: "' + orderMeta.noteText + '", vom ' + formattedDateTime + ', wurde erfolgreich gespeichert');
        } else {
            alert("Bitte füllen Sie das erforderliche Feld aus");
        }

    });
}

function initControlCheckoutButton() {

    setTimeout(function () {

        const button = jQuery('.page-id-7 button');
        const checkbox = jQuery('input[type="checkbox"].woocommerce-form__input');

        button.addClass('disabled');

        jQuery('body').on('click', function () {
            checkbox.prop('checked') ? button.removeClass('disabled') : button.addClass('disabled');
        });
    }, 500)
}

function initDenyOffer() {

    const button = jQuery('.deny-offer');

    button.on('click', function () {
        const requestID = jQuery(this).attr('data-src');
        const message = jQuery('textarea[name="reason_deny_admin_offer"]').val();
        denyAdminOffer(requestID, message);
    })
}

function initTriggerDenyOfferModal() {

    const body = jQuery('body');

    body.on('click', '.close-modal', function () {
        const modal = jQuery('.modal');
        modal.removeClass('active');
    })

    body.on('click', '.open-modal', function () {
        const action = jQuery(this).attr('id');
        jQuery('.' + action + '-modal').addClass('active');
    });

}

function initResetStand() {

    const body = jQuery('body');

    body.on('click', '.trigger-reset-stand-modal', function () {
        const modal = jQuery(this).siblings('.reset-stand-modal');
        modal.addClass('active');
    });

    body.on('click', '.close-modal', function () {
        const modal = jQuery('.reset-stand-modal');
        modal.removeClass('active');
    });

    body.on('click', '.reset-stand', function () {
        const itemToDelete = jQuery(this).attr('id');
        resetStand(itemToDelete);
    });
}

function initExportBookingStatus() {

    const button = jQuery('.export-bookings');
    button.on('click', function () {
        exportBookingStatus();
    });

}

function initShowAccessibleStandsFilter() {
    const checkbox = jQuery('input[name="show-accessible-stands"]');

    checkbox.on('change', function () {
        isChecked = jQuery(this).prop('checked');
        let filterValue = null;
        if (isChecked === true) filterValue = "accessible";

        initGoogleMapsStandsList(filterValue);
    })
}

function initPasswordStrength() {

    const registration = jQuery('.woocommerce-form-register');
    const myAccount = jQuery('.woocommerce-MyAccount-content');
    let passwordField;
    let strengthMeter;
    let registerButton;

    if (registration.length > 0) {
        passwordField = jQuery('#reg_password');
        strengthMeter = jQuery('.password-strength');
        registerButton = jQuery('input[name="register"]');
    }

    if (myAccount.length > 0) {
        passwordField = jQuery('input[name="new_password"]');
        strengthMeter = jQuery('.password-strength');
        registerButton = jQuery('#confirm-change-password');
    }


    if (registration.length > 0 || myAccount.length > 0) {
        passwordField.on('input', function () {
            let password = jQuery(this).val();
            let result = zxcvbn(password);

            let score = result.score;
            let meterWidth = (score + 1) * 20;
            strengthMeter.css('width', meterWidth + '%');

            if (score === 0) {
                strengthMeter.removeClass().addClass('weak');
                if (passwordField.val() === '') {
                    strengthMeter.addClass('shrink');
                }
            } else if (score === 1) {
                strengthMeter.removeClass().addClass('medium');
            } else if (score >= 2) {
                strengthMeter.removeClass().addClass('strong');
            }

            if (result.score < 2) {
                registerButton.prop('disabled', true); // Disable the submit button
            } else {
                registerButton.prop('disabled', false); // Enable the submit button
            }
        });
    }
}

function initDisplayStandTimer() {
    const standTimerWrapper = jQuery('.stand-timer-wrapper');
    const standTimer = jQuery('.stand-timer');

    if (standTimerWrapper.length > 0) {
        let timerInSeconds = 600;

        function updateTimerDisplay() {
            const minutes = Math.floor(timerInSeconds / 60);
            const seconds = timerInSeconds % 60;
            const formattedTime = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            standTimer.text(formattedTime);
        }

        const timerInterval = setInterval(function () {
            if (timerInSeconds > 0) {
                timerInSeconds -= 1;
                updateTimerDisplay();
            } else {
                clearInterval(timerInterval);
                setTimeout(function () {
                    location.reload();
                }, 1000);
            }
        }, 1000);
    }
}

function initShowCurrentStandSelection() {
    const container = jQuery('.show-current-selection');
    const modal = jQuery('.modal');
    if (container.length > 0) {
        jQuery('body').on('click', '.single-result', function () {
            if (modal.hasClass('active')) {
                renderCurrentStandSelection();
            }
        })
    }
}

function initShowPasswordRequirements() {
    const container = jQuery('.password-requirements-container');
    const trigger = jQuery('.password-requirements-container div:first-child');
    const element = jQuery('.password-requirements-container div:last-child');

    if (container.length) {
        trigger.on('click', function () {
            element.toggleClass('active');
        })
    }
}

function initGenerateUserInvoice() {

    jQuery('body').on('click', '.generate-user-invoice', function () {
        const elementID = jQuery(this).attr('id');
        const invoiceID = elementID.split('-')[1];
        printUserInvoice(invoiceID);
    })
}

function initShowFeedbackForm() {
    const button = jQuery('.trigger-contact-form');
    const form = jQuery('.contact-form-content');

    button.on('click', function () {
        form.toggleClass('active');
        jQuery(this).toggleClass('active');
        const isActive = form.hasClass('active');
        const buttonText = isActive ? 'Formular ausblenden' : 'Formular anzeigen';
        button.text(buttonText);
    });
}

function initPrintAllInvoices() {
    const button = jQuery('.download-all-invoices');
    if (button.length > 0) {
        button.on('click', function () {
            printAllInvoices();
        })
    }
}

function initConditionalStandSelection() {
    const container = jQuery('.stand-size-selection-wrapper');
    const standSizeContainer = jQuery('.stand-size');
    const standSizeVerein = jQuery('#stand-size-verein');
    if (container.length > 0) {
        const radioValue = jQuery('.radio-selection input[name="space-required"]');
        const standDepthField = jQuery('input[name="stand_depth"]');
        const standWidthField = jQuery('input[name="stand_width"]');
        radioValue.on('change', function () {
            const value = jQuery(this).val();
            if (value === "more") {
                standSizeContainer.addClass('active');
                standWidthField.val("");
                standDepthField.val("");
            }
            if (value === "equal") {
                standSizeContainer.removeClass('active');
                standWidthField.val("3");
                standDepthField.val("0,60");
            }
        })
    }
}

function initBurgerMenu() {
    const button = jQuery('.burger-menu');
    const navContainer = jQuery('.nav-menu');
    const slidePage = jQuery('main, footer, .header-content-container, .video-background-wrapper');

    button.on('click', function () {
        button.toggleClass('active');
        navContainer.toggleClass('active');
        slidePage.toggleClass('slide-out')
    })

    jQuery('body').on('click', function (e) {
        if (jQuery('.nav-menu.active').length > 0 && !navContainer.is(e.target) && navContainer.has(e.target).length === 0 && !button.is(e.target) && button.has(e.target).length === 0) {
            navContainer.removeClass('active');
            button.removeClass('active');
        }
    });

}

function initFAQ() {
    const title = jQuery('.faq-title');
    const faqContainer = jQuery('.single-faq');

    title.on('click', function () {
        const thisContainer = $(this).parent();
        faqContainer.not(thisContainer).removeClass('active');
        thisContainer.toggleClass('active');
    });
}

function initMobileMenuNavigation() {

    const listWithChildrenLink = jQuery('#menu-footermenue-mobil .menu-item-has-children > a');

    listWithChildrenLink.removeAttr('href');
    listWithChildrenLink.addClass('pointer');

    jQuery('#menu-footermenue-mobil .menu-item-has-children').click(function (e) {
        e.stopPropagation();
        jQuery(this).toggleClass('flex');
        let $el = $('ul', this);
        jQuery('.mobile-navigation .menu-item-has-children .sub-menu').not($el).slideUp();
        $el.stop(true, true).slideToggle(400);
    });

    jQuery('#menu-footermenue-mobil .menu-item-has-children').on('click', function () {
        let element = $(this);
        element.toggleClass('active');
        jQuery('.menu-item-has-children').not(element).removeClass('active');
    })

}

function initEditCustomerRequest() {

    jQuery('body').on('click', '.edit-request', function () {

        const requestedStand = jQuery(this).attr('data-stand-id');
        const requestID = jQuery(this).attr('data-request-id');
        const requestUserID = jQuery(this).parent().parent().find('input[name="request_user_id"]').val();
        const requestUserName = jQuery(this).parent().parent().find('input[name="request_user_name"]').val();
        const editRequestButton = jQuery('.edit-request');
        const singleRequestContainer = jQuery(this).parent().parent();
        const requestContainer = jQuery('.select-request-container');
        const requestContainerContent = jQuery('.select-request-container-content');
        const input = jQuery('input[name="request_id"]');
        const market_prefix = bookingport_plugin_settings['market_prefix'];

        // hide the customer selection field but fill it with the user ID from the request
        const selectCustomerInput = jQuery('input[name="select_customer"]');
        const selectCustomerContainer = jQuery('.select-customer-container');
        selectCustomerContainer.addClass('is-edit');
        selectCustomerInput.attr('data-attribute', requestUserID);
        selectCustomerInput.removeAttr('required');

        input.val(requestID);
        requestContainer.addClass('active');
        requestContainerContent.html('<h3>Sie bearbeiten derzeit Anfrage ' + market_prefix + requestID + ' für ' + requestUserName + '</h3>');

        editRequestButton.removeClass('active');
        editRequestButton.parent().parent().removeClass('active');
        jQuery(this).addClass('active');
        singleRequestContainer.addClass('active');
        preCartItems.add(requestedStand);
        addItemsPreCart(Array.from(preCartItems), true);
    })
}

function initTriggerExpiredOfferModals() {

    const body = jQuery('body');

    body.on('click', '.open-reactivate-offer-modal', function () {
        const reactivateModal = jQuery(this).parent().siblings('.reactivate-offer-modal');
        reactivateModal.addClass('active');
    })

    body.on('click', '.open-delete-offer-modal', function () {
        const deleteModal = jQuery(this).parent().siblings('.delete-offer-modal');
        deleteModal.addClass('active');
    })
}

function initDeleteOffer() {

    jQuery('body').on('click', '.delete-offer', function () {
        const offerToDelete = jQuery(this).attr('data-src');
        deleteOffer(offerToDelete);
    })
}

function initFilterExpiredOffers() {
    const searchInput = jQuery('input[name="filter_expired_offers"]');
    searchInput.on('keyup', inputLimiter(function (e) {
        e.preventDefault();
        const searchValue = jQuery(this).val();
        filterExpiredOffers(searchValue);
    }, 300));
}

function filterExpiredOffers(searchValue) {
    const container = jQuery('.expired-offers-container');
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'filter_expired_offers', searchValue: searchValue
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (res) {
            container.html(res);
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}


function deleteOffer(offerToDelete) {
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'delete_offer', offerToDelete: offerToDelete
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (res) {
            window.location.reload();
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}

function initReactivateOffer() {

    jQuery('body').on('click', '.reactivate-offer', function () {
        const offerToReactivate = jQuery(this).attr('data-src');
        reactivateOffer(offerToReactivate);
    })
}

function reactivateOffer(offerToReactivate) {
    jQuery.ajax({
        url: ajax.url, method: 'POST', dataType: 'json', data: {
            action: 'reactivate_offer', offerToReactivate: offerToReactivate
        }, beforeSend: function () {
            // Show loader or loading state if needed
        }, success: function (res) {
            window.location.reload();
        }, complete: function () {
            // Hide loader or loading state if needed
        }
    });
}
