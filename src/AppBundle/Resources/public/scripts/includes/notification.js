const notification = {

    duration: 6000, // 1000 = 1 second

    // hide notification behaviour
    initAlerts: function() {
        let $alerts = $(".notifications .alert");
        $alerts.addClass("is-visible"); // animate in

        let self = this;
        $alerts.each(function(index, alert) {

            setTimeout(function(){
                self.removeAlert(alert); // remove with time
            }, self.duration);

            $(this).find('.close').on('click', function() {
                self.removeAlert(alert); // remove on click
            });
        });
    },

    // remove alert from DOM
    removeAlert: function(alert) {
        $(alert).removeClass('is-visible'); // animate out

        // wait 1 second for the animation to be done
        setTimeout(function(){
            $(alert).remove(); // remove from DOM
        }, 1000);
    },

    /**
     * helper to create notifications
     * uses Bootstrap classes: "success", "warning", "danger", eg.:
     * notification.createAlert("Hello world!", "success");
     */
    createAlert: function(message, type) {
        let $notifications = $(".notifications");
        let $alert = "<div class='alert alert-" + type + "'><span>" + message + "</span><i class='close-notification fa fa-close' data-dismiss='alert'></i></div>";

        $notifications.append($alert);

        let self = this;

        // let some time for animation
        setTimeout(function() {
            self.initAlerts();
        }, 100);
    }
};

