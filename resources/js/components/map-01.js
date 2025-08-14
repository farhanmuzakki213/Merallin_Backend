import jsVectorMap from "jsvectormap";
import "jsvectormap/dist/maps/world";

const map01 = (mapMarkers = []) => {
    const mapSelectorOne = document.querySelectorAll("#mapOne");

    if (mapSelectorOne.length) {
        const mapOne = new jsVectorMap({
            selector: "#mapOne",
            map: "world",
            zoomButtons: false,

            regionStyle: {
                initial: {
                    fontFamily: "Outfit",
                    fill: "#D9D9D9",
                },
                hover: {
                    fillOpacity: 1,
                    fill: "#465fff",
                },
            },
            markers: mapMarkers,

            markerStyle: {
                initial: {
                    strokeWidth: 1,
                    fill: "#465fff",
                    fillOpacity: 1,
                    r: 4,
                },
                hover: {
                    fill: "#465fff",
                    fillOpacity: 1,
                },
                selected: {},
                selectedHover: {},
            },

            onRegionTooltipShow: function (tooltip, code) {
                if (code === "EG") {
                    tooltip.selector.innerHTML =
                        tooltip.text() + " <b>(Hello Russia)</b>";
                }
            },
        });
    }
};

export default map01;
