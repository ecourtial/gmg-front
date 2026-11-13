function areYouSure(message) {
    var bnlState = false;

    if (confirm(message) == true) {
        bnlState = true;
    }

    return bnlState;
}
