/**
 * permuteStrings - for a given array of strings, returns an array containing all unique permutations of concatenating those strings
 * @param {string[]} stringArray - an array of strings to permute
 * @return {string[]} - all unique permutations of the given strings
 */
function permuteStrings(stringArray){
    //  check for base case of only one string remaining
    if (stringArray.length <= 1){
        return stringArray;
    }
    //  initialize output array
    var stringPermutations = [];
    //  initialize used string cache
    var cache = {};

    //  loop through stringArray, finding each unique starting string
    for (var i = 0; i < stringArray.length; i++){
        //  save a copy of starting string
        var startingString = stringArray[i];
        //  check starting string to see if it has already been used
        if (!cache.hasOwnProperty(startingString)){
            //  add starting string to cache
            cache[startingString] = null;
            //  clone stringArray and splice out startingString
            var stringSubArray = stringArray.slice();
            stringSubArray.splice(i, 1);
            //  get all permutations of stringSubArray
            var stringSubPermutations = permuteStrings(stringSubArray);
            //  loop through stringSubPermutations, pushing each permutation to stringPermutations
            for (var j = 0; j < stringSubPermutations.length; j++) {
                stringPermutations.push(startingString + stringSubPermutations[j]);
            }
        }
    }

    //  return list of permutations
    return stringPermutations;
}