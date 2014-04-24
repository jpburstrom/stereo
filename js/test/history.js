test("Check dependencies", function() {
    ok(_, "We have Underscore");
    ok(Backbone, "We have Backbone");
});

test("Check Stereo object", function() {
    ok(window.Stereo, "Stereo");
    ok(window.Stereo.HistoryRouter, "History Router");
});

