import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm';

const SUPABASE_URL = 'https://hvwmkcvxgjcosusalxjz.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imh2d21rY3Z4Z2pjb3N1c2FseGp6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODk1Njg2OTAsImV4cCI6MjEwNTE0NDY5MH0.aFC900DDBCXMs5d8EbmMyq_0bxiznz7ynCU3l1WGBKc';

export const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
