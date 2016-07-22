/**
 * permuteStrings - for a given array of strings, returns an array containing all unique permutations of those strings, concatenated
 * @param {string[]} stringArray - an array of strings to permute
 * @return {string[]} - all unique permutations of the given strings
 */
function permuteStrings(stringArray){

    //  check for base case of only zero-to-one string remaining
    if (stringArray.length <= 1){
        return stringArray;
    }

    //  initialize output array and used string cache
    var stringPermutations = [];
    var cache = {};

    //  loop through stringArray, finding each unique starting string
    for (var i = 0; i < stringArray.length; i++){

        var startingString = stringArray[i];
        if (!cache.hasOwnProperty(startingString)){ //  verify starting string has not already been used
            //  cache starting string
            cache[startingString] = null;

            //  obtain unique permutations of remaining strings
            var stringSubArray = stringArray.slice();
            stringSubArray.splice(i, 1);
            var stringSubPermutations = permuteStrings(stringSubArray);

            //  push all permutations, including starting string, to output array
            for (var j = 0; j < stringSubPermutations.length; j++) {
                stringPermutations.push(startingString + stringSubPermutations[j]);
            }
        }
    }

    //  return list of unique permutations
    return stringPermutations;
}