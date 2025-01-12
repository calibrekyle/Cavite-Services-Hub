document.addEventListener("DOMContentLoaded", () => {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                console.log("Latitude:", position.coords.latitude);
                console.log("Longitude:", position.coords.longitude);
                // You can send this data to the server or display it on the UI
            },
            (error) => {
                console.error("Error obtaining location:", error.message);
            }
        );
    } else {
        alert("Geolocation is not supported by your browser.");
    }
});
document.addEventListener("DOMContentLoaded", () => {
    const map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 14.4793, lng: 120.8964 }, // Default location (e.g., Cavite City)
        zoom: 15,
    });

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
                map.setCenter(userLocation);
                new google.maps.Marker({
                    position: userLocation,
                    map: map,
                    title: "Your Location",
                });
            },
            (error) => console.error("Error getting location:", error)
        );
    }
});
