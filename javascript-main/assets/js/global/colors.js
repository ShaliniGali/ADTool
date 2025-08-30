"use strict";

	//
	// 	Lea 
	//	Updated: 22 may 2020
	// changes into class format jan 27 2021
	//


	class Colors {

	    /**
	     * returs an array of colors of length num in linear scaeling between the string colors in colors
	     * ex. new Colors().colorGradient(15, ["#ebff95", "#ff0032"])
	     * returns ['#ebff95', '#effc97', '#f4f895', '#f7f290', '#fbeb88', '#fee37e', '#ffd973', '#ffcd66', '#ffc059', '#ffb04d', '#ff9e42', '#ff8939', '#ff7033', '#ff4e31', '#ff0032']
	     * 
	     * @param {int} steps 
	     */
	    colorGradient(steps, colors) {
	        let clr = d3.scaleLinear()
	            .range(colors)
	            .domain([0, steps - 1]);

	        let colourArray = d3.range(steps).map(function(d) {
	            return clr(d)
	        })
	        return colourArray
	    }

	    get green() {
	        return '#28bfa4'
	    }
	    get red() {
	        return '#ff0032'
	    }
	    get yellow() {
	        return '#ebff95'
	    }

	    /**
	     * colors for graphs with low point counts
	     */
	    get LowCountPointColors() {
	        return ['#0067bf', '#62a6d6', '#96d7e8', '#c6f5f6', '#f1ffff']
	    }

	    /**
	     * colors for graphs with high point counts
	     */
	    get HighCountPointColors() {
	        return ['#55efc4', '#ffeaa7', '#81ecec', '#fab1a0', '#74b9ff', '#ff7675', '#a29bfe', '#fd79a8', '#dfe6e9', '#636e72', '#00b894', '#fdcb6e', '#00cec9', '#e17055', '#0984e3', '#d63031', '#6c5ce7', '#e84393', '#b2bec3', '#2d3436', '#ea5d47', '#e55649', '#df4e4b', '#da464d', '#d53e4f']
	    }

	    get graphBG() {
	        return 'rgba(0,0,0,0)'
	    }

	    liveGraphData() {
	        return {
	            'base': this.HighCountPointColors,
	            'green': '#4daf4a',
	            'db_api': '#377eb8',
	            'email_api': '#e41a1c',
	            'codelab_api': '#984ea3'
	        }
	    }
	}

	if (!window._rb) window._rb = {};
	window._rb.Colors = Colors;
