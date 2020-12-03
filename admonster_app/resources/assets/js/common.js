// グローバルに利用したい関数をインスタンスプロパティに定義する
Vue.prototype.$getObject = function(obj, key, callback, separator='/') {
    var keys = key.split(separator);
    var k = keys.pop();
    if (obj === undefined || obj === null) {
        obj = [];
    } else if (!(obj instanceof Array)) {
        obj = [obj];
    }
    obj = keys.reduce(function (obj, key) {
        return obj.reduce(function (ary, obj) {
            if (obj[key] !== undefined) {
                ary = ary.concat(obj[key]);
            }
            return ary;
        }, []);
    }, obj);
    return callback(obj, k);
}

Vue.prototype.$getValue = function(obj, key, val='', separator='/') {
    return this.$getObject(obj, key, function (obj, key) {
        obj = obj[0];
        if (obj === undefined | obj === null | obj[key] === undefined) {
            return val;
        } else {
            return obj[key];
        }
    }, separator);
}
