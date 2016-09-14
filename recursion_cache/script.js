/**
 * VolleyballScoreSequences -
 * @constructor
 */
function VolleyballScoreSequences() {
    "use strict";
    var self = this;
    //  object to cache known scores
    var scoreCache = {};
    cacheResult(0, 0, 1);

    /**
     * getCache - returns the cache for inspection
     * @returns {Object}
     */
    this.getCache = function(){
        return scoreCache;
    };

    this.getSequences = function(scoreA, scoreB){

        //  check the cache to see if score was already evaluated
        var cachedValue  = checkCacheResult(scoreA, scoreB);
        if (cachedValue !== false){
            return cachedValue;
        }

        //  check to see which precursor scores are valid
        var child1Valid = checkValidScore(scoreA - 1, scoreB);
        var child2Valid = checkValidScore(scoreA, scoreB - 1);

        //  cache results based upon valid children
        if (child1Valid && child2Valid) {
            cacheResult(scoreA, scoreB, self.getSequences(scoreA - 1, scoreB) + self.getSequences(scoreA, scoreB - 1));
        } else if (child1Valid) {
            cacheResult(scoreA, scoreB, self.getSequences(scoreA - 1, scoreB));
        } else if (child2Valid) {
            cacheResult(scoreA, scoreB, self.getSequences(scoreA, scoreB - 1));
        } else {
            return 0;
        }

        //  return cached result
        return checkCacheResult(scoreA, scoreB);
    };

    /**
     * checkCacheResult -
     * @param scoreA
     * @param scoreB
     * @returns {*}
     */
    function checkCacheResult(scoreA, scoreB){
        if (scoreCache.hasOwnProperty(scoreA) && scoreCache[scoreA].hasOwnProperty(scoreB)){
            return scoreCache[scoreA][scoreB];
        }
        return false;
    }

    /**
     * checkValidScore -
     * @param scoreA
     * @param scoreB
     * @returns {boolean}
     */
    function checkValidScore(scoreA, scoreB){
        if (scoreA < 0 || scoreB < 0){ // either score is negative
            return false;
        } else if (scoreA < 25 && scoreB < 25) { // neither score is high enough to win
            return true;
        } else if (Math.abs(scoreA - scoreB) < 2) { // at least one score is above 25, but both are less than 2 apart
            return true;
        }
        return false; // scores are outside allowable game conditions
    }

    /**
     * cacheResult -
     * @param scoreA
     * @param scoreB
     * @param sequenceCount
     * @returns {*}
     */
    function cacheResult(scoreA, scoreB, sequenceCount){
        if(!scoreCache.hasOwnProperty(scoreA)) {
            scoreCache[scoreA] = {};
        }
        scoreCache[scoreA][scoreB] = sequenceCount;
        return sequenceCount;
    }
}

var scores = new VolleyballScoreSequences();