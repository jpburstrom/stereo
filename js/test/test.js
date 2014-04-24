/*
	<script src="../src/jquery.yuckbox-links.js"></script>
	<script src="../src/jquery.yuckbox-history.js"></script>
	<script src="../src/jquery.yuckbox-controls.js"></script>
*/

//QUnit.config.reorder = false;
//QUnit.config.autostart = false;

window.soundManager = new SoundManager();
soundManager.setup({ url: "../src/vendor/soundmanager2/swf" });
window.testFilePath = "rain.mp3";
Stereo.options.urlRoot = "../_mp3/";
Stereo.options.infoURL = "info.php?f=";

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
        var s = new Stereo.Song(testFilePath, { id: "song-checks" } ) ;
        s.play();
        ok(s.snd, "Play creates sound object");
        s.pause();
        ok(s.snd, "Pause leaves it alone");
        strictEqual(s.snd, soundManager.sounds["song-checks"], "Song.snd and SM objects are equal");
        s.stop();
        strictEqual(s.snd, undefined, "Stop destroys it");
        strictEqual(_.contains(soundManager.soundIDs, "song-checks"), false, "Stop destroys it in soundManager as well");
    });

    test("Playlist add play remove prev next", function() {

        equal(Stereo.playlist.length, 0, "Playlist is empty");
        Stereo.playlist.add( testFilePath, { id: "myid2" } );
        equal(Stereo.playlist.length, 1, "add is working");
        Stereo.player.play();
        ok(Stereo.playlist.first().snd, "Play creates sound object");
        strictEqual(Stereo.player.get('song'), testFilePath, "Player song == " + testFilePath);
        strictEqual(Stereo.player.get('song'), Stereo.playlist.first().id, "Player song and playlist first song id are equal");
        strictEqual(Stereo.playlist.first().snd, soundManager.sounds.myid2, "Song.snd and SM objects are equal");
        Stereo.playlist.remove( testFilePath );
        equal(Stereo.playlist.length, 0, "remove is working");
        equal(Stereo.player.isPlaying(), false, "isPlaying returns false after song remove");
        equal(Stereo.player.get('song'), false, 'Player song is false');
        strictEqual(undefined, soundManager.sounds.myid2, "remove is also destroying sm object");

        Stereo.playlist.add( ["hello", "dolly"] );
        equal(Stereo.playlist.length, 2, "add is working");
        Stereo.player.set('song', "yadaddadad");
        Stereo.player.next();
        equal(Stereo.player.get('song'), "hello", "If songid is non-existing, next() sets first song");
        Stereo.playlist.reset();


    });

    /*
    asyncTest("Player states: Play pause stop", function() {
        var s;
        equal(Stereo.playlist.length, 0, "Playlist is empty");
        Stereo.playlist.add( testFilePath, { id: "myid3" } );
        s = Stereo.playlist.at(0);
        s.once('play', function() {
            start();
            setTimeout(function() {
                ok(Stereo.player.isPlaying(), "Player is playing on play");
                Stereo.player.pause();
            }, 0);
        });
        s.once('pause', function() {
            start();
            setTimeout(function() {
                ok(Stereo.player.isPaused(), "Player is playing on play");
                Stereo.player.stop();
            }, 0);
        });
        s.once('stop', function() {
            start();
            setTimeout(function() {
                ok(Stereo.player.isStopped(), "Player is playing on play");
                Stereo.playlist.remove( testFilePath );
            }, 0);
        });
        Stereo.player.play();
    });
    */
    asyncTest("getSong/getSongSafe", function() {
        var s;
        start();
        Stereo.playlist.add( testFilePath, { id: "myid3" } );
        equal(Stereo.playlist.length, 1, "Length is 1");
        equal(Stereo.playlist.first().id, testFilePath, "Id is correct");
        Stereo.player.set('song', false);
        s = Stereo.player.getSongSafe();
        equal(s.id, Stereo.player.get('song'));
        equal(Stereo.player.getSong().id, testFilePath);
        Stereo.player.set('song', false);
        s = Stereo.player.getSong();
        equal(Stereo.player.get('song'), false);
    });

    asyncTest("PlaylistItem View Play", function() {
        var v, s, $f = $("#playlistItem");
        Stereo.playlist.reset();
        Stereo.playlist.add([testFilePath, "dummy"]);
        s = Stereo.player.getSongSafe();
        v = new Stereo.View.PlaylistItem({
            url: s.id,
            el: "#playlistItem"
        });

        s.once('play', function() {
            setTimeout(function() {
                start();
                ok($f.hasClass('playing'), "Has playing on play");
                Stereo.playlist.reset();
                Stereo.player.stop();
            }, 0);
        });

        Stereo.player.play();

    });

    asyncTest("PlaylistItem View Pause", function() {
        var v, s, $f = $("#qunit-fixture");
        Stereo.playlist.reset();
        Stereo.playlist.add([testFilePath, "dummy"]);
        s = Stereo.player.getSongSafe();
        v = new Stereo.View.PlaylistItem({
            url: s.id,
            el: "#qunit-fixture"
        });

        s.once('pause', function() {
            setTimeout(function() {
                start();
                equal(Stereo.player.get('playState'), 2);
                ok($f.hasClass('paused'), "Has paused on pause");
                Stereo.playlist.reset();
            }, 0);
        });

        s.once('play', function() {
            Stereo.player.pause();
        });


        Stereo.player.play();

    });

    asyncTest("PlaylistItem View Stop", function() {
        var v, s, $f = $("#qunit-fixture");
        Stereo.playlist.reset();
        Stereo.playlist.add([testFilePath, "dummy"]);
        s = Stereo.player.getSongSafe();
        v = new Stereo.View.PlaylistItem({
            url: s.id,
            el: "#qunit-fixture"
        });

        s.once('play', function() {
            setTimeout(function() {
                start();
                ok($f.hasClass('stopped'), "Has stopped on stop");
                Stereo.playlist.reset();
            }, 0);
        });

        Stereo.player.play();
        Stereo.player.stop();

    });

    asyncTest("PlaylistItem View Inactive", function() {
        var v, s, $f = $("#qunit-fixture");
        Stereo.playlist.reset();
        Stereo.playlist.add([testFilePath, "dummy"]);
        s = Stereo.player.getSongSafe();
        v = new Stereo.View.PlaylistItem({
            url: s.id,
            el: "#qunit-fixture"
        });

        Stereo.player.once('change', function() {
            setTimeout(function() {
                start();
                ok(_.isEmpty($f[0].className.trim()));
                Stereo.playlist.reset();
            }, 0);
        });

        Stereo.player.play();
        Stereo.player.next();

    });

    asyncTest("PlaylistItem check click", function() {
        var v, s, $f = $("#qunit-fixture");
        Stereo.playlist.reset();
        Stereo.playlist.add(testFilePath);
        s = Stereo.player.getSongSafe();
        v = new Stereo.View.PlaylistItem({
            url: s.id,
            el: "#qunit-fixture"
        });

        s.once('play', function() {
            start();
            ok(Stereo.player.isPlaying());
            Stereo.playlist.reset();
        });

        $f.click();
    });

    asyncTest("PlaylistItem check click twice pause toggle", function() {
        var v, s, $f = $("#qunit-fixture");
        Stereo.playlist.reset();
        Stereo.playlist.add(testFilePath);
        s = Stereo.player.getSongSafe();
        v = new Stereo.View.PlaylistItem({
            url: s.id,
            el: "#qunit-fixture"
        });

        s.once('pause', function() {
            start();
            ok(Stereo.player.isPaused());
            Stereo.playlist.reset();
        });

        $f.click();
        $f.click();
    });

    asyncTest("PlaylistItem switch", function() {
        var v1, v2, s1, s2, $f = $("#qunit-fixture");
        $f.append("<div id='s1'></div>");
        $f.append("<div id='s2'></div>");
        Stereo.playlist.reset();
        Stereo.playlist.add(testFilePath);
        Stereo.playlist.add(testFilePath + "?v=2");
        s1 = Stereo.playlist.at(0);
        s2 = Stereo.playlist.at(1);
        v1 = new Stereo.View.PlaylistItem({
            url: s1.id,
            el: "#qunit-fixture #s1"
        });
        v2 = new Stereo.View.PlaylistItem({
            url: s2.id,
            el: "#qunit-fixture #s2"
        });

        s1.once('play', function() {
            $f.find("#s2").click();
        });
        s2.once('play', function() {
            start();
            ok(v1.options.url == s1.id);
            ok(s1.id === testFilePath);
            ok(s2.id === testFilePath + "?v=2");
            equal(s2.id, Stereo.player.get('song'));
            ok(_.isEmpty($f.find("#s1")[0].className.trim()), "Check stopped item has no classes");
            ok(_.isEmpty($f.find("#s2").hasClass("playing active"), "Check playing item"));
            Stereo.player.stop();
            Stereo.playlist.reset();
        });

        $f.find("#s1").click();
    });

    asyncTest("Testing song info function", function() {

        var str, s;
        str = "path/to/file";
        s = new Stereo.Song( str );

        s.info.on('hasInfo', function() {
            start();
            ok(this.get('title'), "Title is fetched");
            equal("/" + this.get('id'), this.get('file')); //File is set in info url
        });

        s.info.getInfo();

    });

    /*
    asyncTest("Label view", function () {
        var v, s, $f = $("#qunit-fixture");
        Stereo.playlist.reset();
        Stereo.playlist.add(testFilePath);

        v = new Stereo.View.Label({
            template: _.template("<%= title %> <%= album %>"),
            el: "#qunit-fixture"
        });

        s = Stereo.player.getSong();

        s.info.on('hasInfo', function() {
            setTimeout(function() {
                start();
                equal($f.html().trim(), "Hello World An Album");
                Stereo.player.stop();
                Stereo.playlist.reset();
            }, 0);
        });

        s.info.getInfo();


    });

        */

});

test("Playlist checks", function() {
    var p = new Stereo.Playlist([new Stereo.Song("A"), new Stereo.Song("B"), new Stereo.Song("C")]);
    equal(p.length, 3, "Check playlist length");

    /*
    p.setRepeat(false);
    ok(true, "When repeat is off:");
    strictEqual(p.getPrev(0), false, "Check prev from zero is false");
    strictEqual(p.getPrev(1), "A", "Check prev from one is A");
    strictEqual(p.getNext(1), "C", "Check next from one is C");
    strictEqual(p.getNext(2), false, "Check next from two is false");
    */

    //p.setRepeat(true);
    ok(true, "When repeat is on:");
    strictEqual(p.getPrev(0), "C", "Check prev from zero is two, with repeat");
    strictEqual(p.getPrev(1), "A", "Check prev from one is zero, with repeat");
    strictEqual(p.getNext(1), "C", "Check next from one is two, with repeat");
    strictEqual(p.getNext(2), "A", "Check next from two is zero, with repeat");

    /*
    p.setRepeat(false);
    ok(true, "When repeat is off:");
    strictEqual(p.getPrevById("A"), false, "Check prev from A is false");
    strictEqual(p.getPrevById("B"), "A", "Check prev from B is zero");
    strictEqual(p.getNextById("B"), "C", "Check next from B is two");
    strictEqual(p.getNextById("C"), false, "Check next from C is false");
    */

    //p.setRepeat(true);
    strictEqual(p.getPrevById("A"), "C", "Check prev from A is C");
    strictEqual(p.getNextById("C"), "A", "Check next from C is A");

    strictEqual(p.getPrevById("bobobobo"), "A", "Check prev from gibberish is A");
    strictEqual(p.getNextById("bobobobo"), "A", "Check next from gibberish is A");

    strictEqual(p.getPrevById(false), "A", "Check prev from false is A");
    strictEqual(p.getNextById(false), "A", "Check next from false is A");

    p.reset();
    strictEqual(p.getPrevById(0), false, "Check that empty collection returns false");
    strictEqual(p.getNextById("A"), false, "Check that empty collection returns false");
    strictEqual(p.getPrev(0), false, "Check that empty collection returns false");
    strictEqual(p.getPrev(0), false, "Check that empty collection returns false");


});

test("Testing URI functions", function() {
    var s, str, root;
    root = Stereo.options.urlRoot;
    Stereo.options.urlRoot = "http://testing.com";
    _.each(['http://', 'https://', '//'], function(str) {
        s = new Stereo.Song( "path/to/file" );
        equal(s.url(), "http://testing.com/path/to/file", "Testing url concat, " + str);
        s = new Stereo.Song( "/path/to/file" );
        equal(s.url(), "http://testing.com/path/to/file", "Testing url concat, leading slash, " + str);
        s = new Stereo.Song( "http://foo.com/path/to/file" );
        equal(s.url(), "http://foo.com/path/to/file", "Testing full URL, " + str);
        Stereo.options.urlRoot = "http://testing.com/";
        s = new Stereo.Song( "/path/to/file"  );
        equal(s.url(), "http://testing.com/path/to/file", "Testing url concat, trailing slash, " + str);
    });
    Stereo.options.urlRoot = root;

});


$(function() {
    test("Testing Stereo.init", function() {
        $('#qunit-fixture').append('<div id="target"></div>');
        var f;
        Stereo.init({
            controls: {
                id: "#controls"
            }
        });
        f = $("#controls");

        ok(f.hasClass("stereo-controls"), "Has class");
        ok(f.find('.stereo-buttons').length == 1, 'Has buttons');
        ok(f.find('.stereo-label').length == 1, 'Has label');
        ok(f.find('.stereo-time').length == 1, 'Has time');
        ok(f.find('.stereo-position').length == 1, 'Has position');
    });

});
