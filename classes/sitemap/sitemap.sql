-- DROP TABLE sitemap;

CREATE TABLE sitemap
(
  checksum integer NOT NULL,
  loc character varying NOT NULL,
  host character varying NOT NULL,
  last_mod timestamp with time zone,
  change_freq character varying,
  priority double precision,
  date_added timestamp with time zone NOT NULL DEFAULT now(),
  CONSTRAINT sitemap_pkey PRIMARY KEY (loc)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE sitemap OWNER TO jimmy;


-- Index: sitemap_date_added_idx

-- DROP INDEX sitemap_date_added_idx;

CREATE INDEX sitemap_date_added_idx
  ON sitemap
  USING btree
  (date_added);

-- Index: sitemap_idx_loc

-- DROP INDEX sitemap_idx_loc;

CREATE INDEX sitemap_idx_loc
  ON sitemap
  USING btree
  (checksum, host);
