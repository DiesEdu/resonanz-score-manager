// Master instrument catalog
export const masterInstruments = [
  // Woodwind
  { id: "flute", name: "Flute", family: "woodwind" },
  { id: "oboe", name: "Oboe", family: "woodwind" },
  { id: "clarinet", name: "Clarinet", family: "woodwind" },
  { id: "bassoon", name: "Bassoon", family: "woodwind" },
  // String
  { id: "violin1", name: "Violin 1", family: "string" },
  { id: "violin2", name: "Violin 2", family: "string" },
  { id: "viola", name: "Viola", family: "string" },
  { id: "cello", name: "Cello", family: "string" },
  { id: "contrabass", name: "Contrabass", family: "string" },
  // Brass
  { id: "trumpet", name: "Trumpet", family: "brass" },
  { id: "trombone", name: "Trombone", family: "brass" },
  // Timpani
  { id: "timpani", name: "Timpani", family: "timpani" },
];

// Grouped for UI
export const instrumentFamilies = [
  {
    name: "woodwind",
    label: "Woodwind",
    instruments: masterInstruments.filter((i) => i.family === "woodwind"),
  },
  {
    name: "string",
    label: "Strings",
    instruments: masterInstruments.filter((i) => i.family === "string"),
  },
  {
    name: "brass",
    label: "Brass",
    instruments: masterInstruments.filter((i) => i.family === "brass"),
  },
  {
    name: "timpani",
    label: "Timpani",
    instruments: masterInstruments.filter((i) => i.family === "timpani"),
  },
];

// Example data
export const exampleEvents = [
  {
    id: "ev1",
    name: "Classical Concert",
    songsCount: 4,
    instrumentIds: [
      "flute",
      "oboe",
      "clarinet",
      "bassoon",
      "violin1",
      "violin2",
      "viola",
      "cello",
      "contrabass",
      "trumpet",
      "trombone",
      "timpani",
    ],
    sheets: {
      flute: [
        {
          id: "s1",
          title: "Mozart - Flute Concerto No.1",
          composer: "Mozart",
          fileUrl:
            "https://imslp.org/wiki/Flute_Concerto_No.1%2C_K.313_(Mozart%2C_Wolfgang_Amadeus)",
          notes: "Urtext edition",
        },
      ],
      violin1: [
        {
          id: "s2",
          title: "Bach Violin Concerto in E major",
          composer: "J.S. Bach",
          fileUrl:
            "https://imslp.org/wiki/Violin_Concerto_in_E_major%2C_BWV_1042_(Bach%2C_Johann_Sebastian)",
          notes: "Solo part",
        },
      ],
    },
  },
];
