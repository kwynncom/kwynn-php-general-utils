// QID-insert-10
printjson(db.getCollection('kwcliTest10').insert([
    {'_id' : 1, 'n' : 1}, 
    {'_id' : 2, 'n' : 2}, 
    {'_id' : 3, 'n' : 3}]));

// QID-sum-10
printjson(db.getCollection('kwcliTest10').aggregate(
        [{ $group : { _id : 'sumName', 'sumn' : {'$sum' : '$n'}  }}   ]).toArray());

// QID-drop-final-coll
printjson(db.getCollection('kwcliTest10').drop());

// QID-drop-final-db
printjson(db.dropDatabase());
