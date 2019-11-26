CREATE TABLE IF NOT EXISTS  logs (
  id serial PRIMARY KEY,
  action character(20) not null,
  item character(36),
  user_id character(36),
  payload json,
  date timestamp not null default CURRENT_TIMESTAMP
);
