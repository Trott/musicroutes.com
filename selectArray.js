function selectAll(mySelectArray) {
	for ( var i = 0; i < mySelectArray.length; i++) {
		mySelectObject = document.getElementById(mySelectArray[i]);
		for ( var j = 0; j < mySelectObject.options.length; j++) {
			mySelectObject.options[j].selected = true;
		}
	}
}

function moveOptions(fromSelect, toSelect) {
	while (fromSelect.selectedIndex >= 0) {
		myIndex = fromSelect.selectedIndex;
		fromSelect.options[myIndex].selected = false;
		toSelect.options[toSelect.length] = fromSelect.options[myIndex];
	}

}

function swapOption(fromSelectId, toSelectId) {
	fromSelect = document.getElementById(fromSelectId);
	toSelect = document.getElementById(toSelectId);
	moveOptions(fromSelect, toSelect);
	moveOptions(toSelect, fromSelect);
}

var selectArray = [];