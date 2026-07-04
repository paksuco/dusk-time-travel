(function () {
    'use strict';

    var globalScope;

    if (typeof globalThis !== 'undefined') {
        globalScope = globalThis;
    } else {
        globalScope = window;
    }

    var previousState = globalScope.__duskTimeTravel;

    // When the shim is already installed, reuse the original native Date.
    var NativeDate;

    if (previousState) {
        NativeDate = previousState.native;
    } else {
        NativeDate = globalScope.Date;
    }

    var offset = __DUSK_TARGET_MS__ - NativeDate.now();

    // Travelling again only needs the offset retargeted.
    if (previousState) {
        previousState.offset = offset;

        return;
    }

    var state = {
        native: NativeDate,
        offset: offset
    };

    function fakeNow() {
        return NativeDate.now() + state.offset;
    }

    function FakeDate(value) {
        // Date() called as a plain function returns the date string for "now".
        if (! (this instanceof FakeDate)) {
            var current = new NativeDate(fakeNow());

            return current.toString();
        }

        // new Date() with no arguments returns the traveled time.
        if (arguments.length === 0) {
            return new NativeDate(fakeNow());
        }

        // A single timestamp or date string argument is not shifted.
        if (arguments.length === 1) {
            return new NativeDate(value);
        }

        // Explicit date components are not shifted either.
        var boundArguments = [null].concat(Array.prototype.slice.call(arguments));
        var BoundDate = Function.prototype.bind.apply(NativeDate, boundArguments);

        return new BoundDate();
    }

    // Returning a native instance from the constructor keeps `instanceof Date`
    // and every prototype method working natively.
    FakeDate.prototype = NativeDate.prototype;
    FakeDate.now = fakeNow;
    FakeDate.parse = NativeDate.parse;
    FakeDate.UTC = NativeDate.UTC;

    try {
        Object.setPrototypeOf(FakeDate, NativeDate);
    } catch (error) {
        // Older engines without setPrototypeOf: static inheritance is cosmetic.
    }

    globalScope.Date = FakeDate;
    globalScope.__duskTimeTravel = state;
})();
