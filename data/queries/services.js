db.halls
  .aggregate([
    { $match: { slug: "royal" } },
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
        }
      }
    },
    {
      $match: {
        $or: [
          {
            "services.children": {
              $in: [
                // ObjectId("5d6fb45782f9d22454c8866f")
                // ObjectId("5d6fb49582f9d22454c88670")
              ]
            }
          },
          {
            "services.parents": {
              $in: [
                // ObjectId("5d6fb45782f9d22454c8866f")
                // ObjectId("5d6fb49582f9d22454c88670")
              ]
            }
          }
        ]
      }
    },
    { $replaceRoot: { newRoot: "$services_object" } }
  ])
  .pretty();
