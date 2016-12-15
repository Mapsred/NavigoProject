/**
 * Created by maps_red on 06/10/16.
 */
$(document).ready(function () {
    var nbCheck = 0;

    function isChecked(elmt) {
        return elmt.checked;
    }

    $(".light").click(function () {
        var elmt = this;
        if (nbCheck < 3 || isChecked(elmt) == false) {
            nbCheck = isChecked(elmt) == true ? nbCheck + 1 : nbCheck - 1;
        } else {
            elmt.checked = '';
        }

    });
});