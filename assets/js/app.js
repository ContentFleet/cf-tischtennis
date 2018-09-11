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
var $addSetButton = $('<button type="button" class="add_set_link">Add a set</button>');
var $newLinkLi = $('<li></li>').append($addSetButton);

jQuery(document).ready(function() {
    // Get the ul that holds the collection of sets
    $collectionHolder = $('ul.sets');

    // add the "add a set" anchor and li to the sets ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);


    addSetForm($collectionHolder, $newLinkLi);
    addSetForm($collectionHolder, $newLinkLi);
    addSetForm($collectionHolder, $newLinkLi);

    $addSetButton.on('click', function(e) {
        // add a new set form (see next code block)
        addSetForm($collectionHolder, $newLinkLi);
    });
});

function addSetForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;
    // You need this only if you didn't set 'label' => false in your sets field in TaskType
    // Replace '__name__label__' in the prototype's HTML to
    // instead be a number based on how many items we have
    // newForm = newForm.replace(/__name__label__/g, index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a set" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
}
