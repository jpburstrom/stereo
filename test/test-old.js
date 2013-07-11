/*
	<script src="../src/jquery.yuckbox-links.js"></script>
	<script src="../src/jquery.yuckbox-history.js"></script>
	<script src="../src/jquery.yuckbox-controls.js"></script>
*/

QUnit.config.reorder = false;
QUnit.config.autostart = false;

(function($) {
    var zz = {
        fix: $("#qunit-fixture"),
        smSetup: function() {
        },
        loadSoundA: function() {
            yuckbox.addSong({
                url: "../src/vendor/soundmanager2/demo/_mp3/office_lobby.mp3"

            });
            return soundManager.sounds[soundManager.soundIDs[0]];
        },
        loadSoundB: function() {
            yuckbox.addSong({
                url: "../src/vendor/soundmanager2/demo/_mp3/rain.mp3"
            });
        }
    };

    delete window.yuckbox;
    window.soundManager = new SoundManager();
    window.yuckbox = new YuckBox();
    soundManager.setup({ url: "../src/vendor/soundmanager2/swf" });
    soundManager.onready(function() {

        var sound = zz.loadSoundA();

        QUnit.start();

        module("One sound");

        test("Check that objects are present", function() {
            ok(window.soundManager, "soundmanager object present");
            ok(window.yuckbox, "yuckbox object present");
            equal(soundManager.soundIDs.length, 1, "one sound loaded");
        });

        test("Play/Pause/Stop", function() {
            equal(soundManager.soundIDs.length, 1, "one sound loaded");
            yuckbox.play();
            equal(yuckbox.isPlaying(), true, "yuckbox.isPlaying");
            equal(sound.playState, 1, "SM playState 1");
            yuckbox.pause();
            equal(sound.playState, 1, "SM playState 1");
            equal(sound.paused, true, "paused");
            yuckbox.stop();
            equal(yuckbox.isPlaying(), false);
            equal(sound.playState, 0);
        });
        test("TogglePause", function() {
            equal(soundManager.soundIDs.length, 1, "one sound loaded");
            yuckbox.play();
            equal(yuckbox.isPlaying(), true, "yuckbox.isPlaying");
            yuckbox.togglePause();
            equal(sound.paused, true, "tglpaused");
            equal(sound.playState, 1, "SM playState 1");
            yuckbox.togglePause();
            equal(sound.paused, false, "tgl paused");
            equal(sound.playState, 1, "SM playState 1");
        });
        test("SetVolume", function() {
            equal(soundManager.soundIDs.length, 1, "one sound loaded");
            yuckbox.setVolume(50);
            equal(sound.volume, 50);
        });

        module("Two sound");

        test("Next", function() {
            var snd1, snd2;
            zz.loadSoundB();
            equal(soundManager.soundIDs.length, 2, "Two sounds loaded");
            snd1 = yuckbox.get("currentSong");
            yuckbox.next();
            snd2 = yuckbox.get("currentSong");
            ok(snd1 != snd2);
            yuckbox.next();
            snd2 = yuckbox.get("currentSong");
            ok(snd1 == snd2);
            
        });
        test("Prev", function() {
            equal(soundManager.soundIDs.length, 2, "Two sounds loaded");
            snd1 = yuckbox.get("currentSong");
            yuckbox.prev();
            snd2 = yuckbox.get("currentSong");
            ok(snd1 != snd2);
            yuckbox.prev();
            snd2 = yuckbox.get("currentSong");
            ok(snd1 == snd2);
        });

        module("Events");

        asyncTest("Events", function() {
            expect(0);
            start();
        });


    });

})(jQuery);
