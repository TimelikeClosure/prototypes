$(function ($) {

    /** Create Operations ======================
     *
     */
    var submitBtn = $('#add-student-btn'),
        sgtTableElement = $('#student-table'),
        firebaseRef = new Firebase("https://lfchallenge.firebaseio.com/users/data");

    /** Click handler to submit student information
     * Take values of the student-add-form
     */
    submitBtn.click(function () {
        var studentName = $('#s-name-input').val(),
            studentCourse = $('#s-course-input').val(),
            studentGrade = $('#s-grade-input').val();

        /** Send the values to firebase
         * append a new item to the user list, ensure that the user gets a unique id
         */
        firebaseRef.push({
            name: studentName,
            course: studentCourse,
            grade: studentGrade
        });
        /*use a firebase reference*/
        /*add the method to the firebase reference*/
        /*append the new data from the add-student-form to firebase*/
        clearInputs();
    });

    /* Clear out inputs in the add-student-form */
    function clearInputs() {
        $('#s-name-input').val('');
        $('#s-course-input').val('');
        $('#s-grade-input').val('');
    }

    /** DOM CREATION ================================== */
    function updateDOM(studentSnapShot) {
        var studentObject = studentSnapShot.val();
        var studentObjectId = studentSnapShot.key();
        var studentRow = $("#" + studentObjectId);
        if (studentRow.length > 0) {
            //change current
            studentRow.find(".student-name").text(studentObject.name);
            studentRow.find(".student-course").text(studentObject.course);
            studentRow.find(".student-grade").text(studentObject.grade);
        } else {
            //add new
            var sName = $('<td>', {
                    text: studentObject.name,
                    class: "student-name"
                }),
                sCourse = $('<td>', {
                    text: studentObject.course,
                    class: "student-course"
                }),
                sGrade = $('<td>', {
                    text: studentObject.grade,
                    class: "student-grade"
                }),
            /* Each student gets a unique edit and delete button appended to its row */
                sEditBtn = $('<button>', {
                    class: "btn btn-info edit-btn",
                    'data-id': studentObjectId
                }),
                sEditBtnIcon = $('<span>', {
                    class: "glyphicon glyphicon-pencil"
                }),
                sDeleteBtn = $('<button>', {
                    class: "btn btn-danger delete-btn",
                    'data-id': studentObjectId
                }),
                sDeleteBtnIcon = $('<span>', {
                    class: "glyphicon glyphicon-remove"
                });

            var studentRow = $('<tr>', {
                id: studentObjectId
            });
            sEditBtn.append(sEditBtnIcon);
            sDeleteBtn.append(sDeleteBtnIcon);
            studentRow.append(sName, sCourse, sGrade, sEditBtn, sDeleteBtn);
            sgtTableElement.append(studentRow);
        }
    }
});