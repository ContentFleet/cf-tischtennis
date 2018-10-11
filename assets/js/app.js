/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
var $ = require('jquery');



var $collectionHolder;

// setup an "add a set" link
var $addSetButton = $('<button type="button" class="add_set_link btn btn-info">Add a set</button>');
var $newLinkLi = $('<li></li>').append($addSetButton);

function addSetsToGame() {
    $collectionHolder = $('ul.sets');
    $collectionHolder.append($newLinkLi);
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addSetButton.on('click', function (e) {
        // add a new set form (see next code block)
        addSetForm($collectionHolder, $newLinkLi);
    });
}
jQuery(document).ready(function() {
    addSetsToGame();
    $('#game_users').removeAttr('multiple');
});

function addSetForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;

    newForm = newForm.replace(/__name__/g, index);

    $collectionHolder.data('index', index + 1);

    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
}
