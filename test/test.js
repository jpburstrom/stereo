/*
	<script src="../src/jquery.yuckbox-links.js"></script>
	<script src="../src/jquery.yuckbox-history.js"></script>
	<script src="../src/jquery.yuckbox-controls.js"></script>
*/

//QUnit.config.reorder = false;
//QUnit.config.autostart = false;

window.soundManager = new SoundManager();
soundManager.setup({ url: "../src/vendor/soundmanager2/swf" });

test("Check dependencies", function() {
    ok(_, "We have Underscore");
    ok(Backbone, "We have Backbone");
});

test("Check Stereo object", function() {
    ok(window.Stereo, "Stereo");
    ok(window.Stereo.Player, "Stereo.Player");
    ok(window.Stereo.player, "Stereo.player");
    ok(window.Stereo.playlist);
});

soundManager.onready(function() {
    test("Song checks", function() {
        var s = new Stereo.Song({ id: "my-id"}) ;
        s.play();
        ok(s.snd, "Play creates sound object");
        s.pause();
        ok(s.snd, "Pause leaves it alone");
        strictEqual(s.snd, soundManager.sounds["my-id"], "Song.snd and SM objects are equal");
        s.stop();
        strictEqual(s.snd, false, "Stop destroys it");
        strictEqual(soundManager.soundIDs.length, 0, "Stop destroys it in soundManager as well");
    });

    test("Playlist add play remove", function() {

        equal(Stereo.playlist.length, 0, "Playlist is empty");
        Stereo.playlist.add( { id: "myid2" } );
        equal(Stereo.playlist.length, 1, "add is working");
        Stereo.player.play();
        ok(Stereo.playlist.first().snd, "Play creates sound object");
        strictEqual(Stereo.playlist.first().snd, soundManager.sounds.myid2, "Song.snd and SM objects are equal");
        Stereo.playlist.remove( { id: "myid2" } );
        equal(Stereo.playlist.length, 0, "remove is working");
        strictEqual(undefined, soundManager.sounds.myid2, "remove is also destroying sm object");

    });

});

test("Playlist checks", function() {
    var p = new Stereo.Playlist([new Stereo.Song({id: "A"}), new Stereo.Song({id: "B"}), new Stereo.Song({id: "C"})]);
    equal(p.length, 3, "Check playlist length");

    p.setRepeat(false);
    strictEqual(p.getPrev(0), false, "Check prev from zero is false");
    strictEqual(p.getPrev(1), 0, "Check prev from one is zero");
    strictEqual(p.getNext(1), 2, "Check next from one is two");
    strictEqual(p.getNext(2), false, "Check next from two is false");

    p.setRepeat(true);
    strictEqual(p.getPrev(0), 2, "Check prev from zero is two, with repeat");
    strictEqual(p.getPrev(1), 0, "Check prev from one is zero, with repeat");
    strictEqual(p.getNext(1), 2, "Check next from one is two, with repeat");
    strictEqual(p.getNext(2), 0, "Check next from two is zero, with repeat");

    p.setRepeat(false);
    strictEqual(p.getPrevById("A"), false, "Check prev from A is false");
    strictEqual(p.getPrevById("B"), 0, "Check prev from B is zero");
    strictEqual(p.getNextById("B"), 2, "Check next from B is two");
    strictEqual(p.getNextById("C"), false, "Check next from C is false");

});

