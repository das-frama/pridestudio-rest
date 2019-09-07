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
        name: 1,
        services: 1,
        services_object: {
          _id: 1,
          name: 1,
          children: {
            $filter: {
              input: "$services_object.children",
              as: "child",
              cond: { $in: ["$$child._id", "$services.children"] }
            }
          }
        },
        prices: 1
      }
    },
    {
      $group: {
        _id: "$_id",
        name: { $first: "$name" },
        prices: { $first: "$prices" },
        services: { $push: "$services" },
        services_object: { $push: "$services_object" }
      }
    }
  ])
  .pretty();
