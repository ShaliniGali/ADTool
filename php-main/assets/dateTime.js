"use strict";

const monthsShort = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"];
const monthsLong = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

/**
 * return date in this format
 * 
 * 30/12/2020 23:01:01
 * 
 * can choose to return just date or just time
 * 
 * @this {date objects} date 
 * @param {bool} hasDate 
 * @param {bool} hasTime 
 */

Date.prototype.formatDateTime = function(hasDate = true, hasTime = true) {
    let text = '';
    if (hasDate) {
        text = text + this.toLocaleDateString('en-GB')
    }
    if (hasTime) {
        if (this.getHours() < 10) {
            text = text + " 0" + this.getHours()
        } else {
            text = text + " " + this.getHours()
        }
        if (this.getMinutes() < 10) {
            text = text + ":0" + this.getMinutes()
        } else {
            text = text + ":" + this.getMinutes()
        }
    }
    return text
}

/**
 * returns rthe difference between the date and endDate in this format
 * 
 * 100d 23:12:01
 */
Date.prototype.timeDifference = function(endDate) {
    if (Number.isInteger(endDate)) {
        endDate = new Date(endDate)
    }

    let timePassed = endDate - this
    let seconds = Math.floor(timePassed / 1000) % 60

    let minutes = Math.floor(timePassed / 1000 / 60) % 60

    let hours = Math.floor(timePassed / 1000 / 60 / 60) % 24

    let days = Math.floor(timePassed / 1000 / 60 / 60 / 24)

    let timeText = ''
    if (seconds < 10) {
        timeText = timeText + '0'
    }

    timeText = timeText + seconds
    timeText = minutes + ':' + timeText

    if (minutes < 10) {
        timeText = '0' + timeText
    }

    if (hours > 0) {
        timeText = hours + ':' + timeText
    }

    if (days > 0) {
        timeText = days + 'd ' + timeText
    }
    return (timeText)
}

/**
 * 
 * returns date in Jan/20/2020 format
 * 
 * @param {date objects} jsDate 
 */
Date.prototype.formatMonthDate = function() {
    return monthsShort[this.getMonth()] + '/' + this.getDate() + '/' + this.getFullYear();
}


/**
 * takes time in seconds and returns time object
 * new Date() takes time in milliseconds
 * 
 * @param {int} phpDate 
 */
Date.prototype.phpToJsTimestamp = function() {
    return new Date(this.getTime() * 1000);
}
