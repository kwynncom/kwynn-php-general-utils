// QID-insert-10
printjson(db.getCollection('kwcliTest10').insert([
    {'_id' : 1}, 
    {'_id' : NumberLong('2'), 'v' : 'standalone'}, 
    {'_id' : 3}]));

// QID-sum-10
printjson(db.getCollection('kwcliTest10').aggregate(
        [{ $group : { _id : 'sumName', 'sumn' : {'$sum' : '$_id'}  }}   ]).toArray());

// QID-drop-final-coll
printjson(db.getCollection('kwcliTest10').drop());

// QID-drop-final-db
printjson(db.dropDatabase());
