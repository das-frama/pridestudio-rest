db.halls
  .aggregate([
    {
      $match: { _id: ObjectId("5d6665acd174792330992eca") }
    },
    { $unwind: "$services" },
    {
      $lookup: {
        from: "services",
        localField: "services.category_id",
        foreignField: "_id",
        as: "services_object"
      }
    },
    { $unwind: "$services_object" },
    {
      $project: {
        _id: 1,
        services_object: {
          _id: 1,
          name: 1,
          children: 1
        }
      }
    },
    {
      $replaceRoot: { newRoot: "$services_object" }
    }
  ])
  .pretty();
